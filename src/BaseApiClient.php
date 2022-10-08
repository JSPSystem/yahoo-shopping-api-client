<?php

namespace JSPSystem\YahooShoppingApiClient;

use CurlHandle;
use SimpleXMLElement;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;
use JSPSystem\YahooShoppingApiClient\Exception\TokenException;

/**
 * APIにリクエストするための抽象クラスです。
 */
abstract class BaseApiClient
{
    private const CONTENT_TYPE_FORM  = 'application/x-www-form-urlencoded';
    private const CONTENT_TYPE_JSON  = 'application/json';
    private const CONTENT_TYPE_MULTI = 'multipart/form-data';

    private const METHOD_GET  = 'GET';
    private const METHOD_POST = 'POST';
    private const METHOD_PUT  = 'PUT';

    private CurlHandle $ch;
    private string $content_type;

    /**
     * レスポンスヘッダー
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * レスポンスボディ
     *
     * @var string|null
     */
    protected ?string $body = null;

    /**
     * BaseApiClient constructor.
     *
     * @param string $access_token YID連携で取得したアクセストークン
     * @param string $cert 証明書
     * @param string $ssl_key 秘密鍵
     */
    public function __construct(
        private string $access_token = '',
        private string $cert = '',
        private string $ssl_key = '',
    ) {
        $this->content_type = self::CONTENT_TYPE_JSON;
    }

    /**
     * リクエストを実行します。
     *
     * @param array $parameters リクエストパラメーター
     * @return array
     */
    abstract public function request(array $parameters): array;

    /**
     * セラーIDに対応したURLを取得します。
     *
     * @param string $seller_id セラーID
     * @param string $url 本番用URL
     * @param string $test_url テスト用URL
     * @return string
     */
    protected function getUrl(string $seller_id, string $url, string $test_url): string
    {
        return strpos($seller_id, 'snbx-') === false ? $url : $test_url;
    }

    /**
     * リクエストにJSONが含まれることを示します。
     *
     * @return BaseApiClient
     */
    protected function asJson(): BaseApiClient
    {
        $this->content_type = self::CONTENT_TYPE_JSON;
        return $this;
    }

    /**
     * リクエストにフォームパラメータが含まれることを示します。
     *
     * @return BaseApiClient
     */
    protected function asForm(): BaseApiClient
    {
        $this->content_type = self::CONTENT_TYPE_FORM;
        return $this;
    }

    /**
     * リクエストがマルチパートリクエストであることを示します。
     *
     * @return BaseApiClient
     */
    protected function asMultipart(): BaseApiClient
    {
        $this->content_type = self::CONTENT_TYPE_MULTI;
        return $this;
    }

    /**
     * GETリクエストを実行します。
     *
     * @param string $url リクエストするURL
     * @param array $query 送信するパラメーター
     * @return array
     */
    protected function get(string $url, array $query = []): array
    {
        try {
            // 初期化・オプション設定
            $this->init();
            $this->setOptions(
                $url . (!empty($query) ? '?' . http_build_query($query) : '')
            );
            // リクエスト実行
            return $this->run();

        } finally {
            $this->close();
        }
    }

    /**
     * POSTリクエストを実行します。
     *
     * @param string $url リクエストするURL
     * @param string|array $parameters 送信するパラメーター
     * @return array
     */
    protected function post(string $url, $data): array
    {
        try {
            // 初期化・オプション設定
            $this->init();
            $this->setOptions($url, $data, self::METHOD_POST);
            // リクエスト実行
            return $this->run();

        } finally {
            $this->close();
        }
    }

    /**
     * PUTリクエストを実行します。
     *
     * @param string $url
     * @param string|array $data
     * @return array
     */
    protected function put(string $url, $data): array
    {
        try {
            // 初期化・オプション設定
            $this->init();
            $this->setOptions($url, $data, self::METHOD_PUT);
            // リクエスト実行
            return $this->run();

        } finally {
            $this->close();
        }
    }

    /**
     * レスポンスボディを配列へパースします。
     *
     * @return array
     */
    protected function parseResponseToArray(): array
    {
        // レスポンスボディのXMLをJSONへ変換
        $json = $this->convertXmlToJson();
        if (false === $json) {
            // XMLでない場合はレスポンスボディがJSONのため、そのまま使用
            $json = $this->body;
        }
        // JSONをデコード
        $json_response = json_decode($json, true);

        // レスポンスが空
        if (!$json_response) {
            throw new ApiException('no response', 'Failed to get the response body');
        }
        // 共通エラー・APIエラー
        if (isset($json_response['Message'])) {
            $code  = isset($json_response['Code']) ? $json_response['Code'] : '';
            if (!empty($code)) {
                $code .= ': ';
            }
            $error = $json_response['Message'];
            throw new ApiException($error, $code . $error);
        }
        if (isset($json_response['error'])) {
            $error = $json_response['error']['reason'] ?? 'api error';
            throw new ApiException($error);
        }

        return $json_response;
    }

    /**
     * レスポンスボディのXMLをJSONに変換します。
     * XMLでない場合はfalseを返します。
     *
     * @return string|false
     */
    protected function convertXmlToJson()
    {
        // XMLのエラーを抑制
        libxml_use_internal_errors(true);
        // XMLをパース
        $xml = simplexml_load_string($this->body, null, LIBXML_NOCDATA);
        // JSON または false を返す
        return false !== $xml ? json_encode($xml) : false;
    }

    /**
     * パラメータからXMLを作成します。
     *
     * @param string $root XMLのルートタグ。例) '<Req></Req>'
     * @param array $parameters パラメータ
     * @return SimpleXMLElement
     */
    protected function convertArrayToXml(string $root, array $parameters): SimpleXMLElement
    {
        $xml = new SimpleXMLElement($root);
        $this->recursiveAddXmlChildren($xml, $parameters);
        return $xml;
    }

    /**
     * XMLに子ノードを追加します（再帰）
     *
     * @param SimpleXMLElement $xml
     * @param mixed $children
     * @return void
     */
    private function recursiveAddXmlChildren(SimpleXMLElement $xml, $children): void
    {
        foreach ($children as $child_column => $child_val) {
            // 値が配列の場合は子ノードを追加して再帰
            if (is_array($child_val)) {
                $child_values = isset($child_val[0]) ? $child_val : [$child_val];
                foreach ($child_values as $value) {
                    $xml_col = $xml->addChild($child_column);
                    $this->recursiveAddXmlChildren($xml_col, $value);
                }
                continue;
            }
            // ノードと値を追加
            $xml->addChild($child_column, $child_val);
        }
    }

    /**
     * 初期化を行います。
     *
     * @return void
     */
    private function init(): void
    {
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);

        // ヘッダ
        $headers = ['Content-Type: ' . $this->content_type];
        if (!empty($this->access_token)) {
            $headers[] = "Authorization: Bearer {$this->access_token}";
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        // 証明書・秘密鍵
        if (!empty($this->cert)) {
            curl_setopt($this->ch, CURLOPT_SSLCERT, $this->cert);
        }
        if (!empty($this->ssl_key)) {
            curl_setopt($this->ch, CURLOPT_SSLKEY, $this->ssl_key);
        }
    }

    /**
     * メソッド別のオプションを設定します。
     *
     * @param string $url
     * @param string|array $data
     * @param string $method
     * @return void
     */
    private function setOptions(
        string $url,
        $data = null,
        string $method = self::METHOD_GET
    ): void
    {
        // データ整形
        $data = $this->formatData($data);
        // URL
        curl_setopt($this->ch, CURLOPT_URL, $url);
        // メソッド別設定
        switch ($method) {
            case self::METHOD_POST:
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
                break;
            case self::METHOD_PUT:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                return;
        }
    }

    /**
     * Content-Typeに合わせたデータ整形を行います。
     *
     * @param string|array $data
     * @return void
     */
    private function formatData($data)
    {
        if (empty($data)) {
            return $data;
        }

        switch ($this->content_type) {
            case self::CONTENT_TYPE_FORM:
                // 配列だとContent-Typeが自動で変わってしまうため文字列へ
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
                return $data;
            case self::CONTENT_TYPE_JSON:
                return json_encode($data);
            default:
                return $data;
        }
    }

    /**
     * リクエストを実行します。
     *
     * @return array
     */
    private function run(): array
    {
        // リクエスト
        $result = curl_exec($this->ch);
        $info   = curl_getinfo($this->ch);

        // レスポンスをヘッダとボディに分割
        $this->extractResponse($result, $info);
        // WebAPIの認証エラーかどうか
        $this->checkAuthError();
        // レスポンスを配列へパース
        return $this->parseResponseToArray();
    }

    /**
     * cURLセッションを閉じます。
     *
     * @return void
     */
    private function close(): void
    {
        if (empty($this->ch)) {
            return;
        }
        curl_close($this->ch);
    }

    /**
     * レスポンスをヘッダとボディに分割します。
     *
     * @param string $raw_response
     * @param array $info
     * @return void
     */
    private function extractResponse($raw_response, $info): void
    {
        // ヘッダとボディを分割
        $header = substr($raw_response, 0, $info['header_size']);
        $header = preg_replace("/(\r\n\r\n)$/", "", $header);
        $body   = substr($raw_response, $info['header_size']);

        // ヘッダを連想配列へ変換
        $header_array = preg_split("/\r\n/", $header);
        $header_array = array_map("trim", $header_array);
        $headers      = [];
        foreach ($header_array as $value) {
            if (preg_match("/HTTP/", $value)) {
                $headers[0] = $value;
            } elseif (!empty($value)) {
                $tmp              = preg_split("/: /", $value);
                $headers[$tmp[0]] = $tmp[1];
            }
        }

        $this->headers = $headers;
        $this->body    = $body;
    }

    /**
     * レスポンスヘッダーからWebAPIで認証エラーが発生したか調べます。
     *
     * @return void
     */
    private function checkAuthError(): void
    {
        // ユーザー認証エラーはWWW-Authenticateヘッダーで返される
        $key  = 'WWW-Authenticate';
        $head = array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
        // WWW-Authenticateヘッダーが無い or 値がNULLであればエラーは発生していない
        if (null === $head) {
            return;
        }
        // WWW-Authenticateヘッダーがあり、値が入っていればエラー
        throw new TokenException($head);
    }

}
