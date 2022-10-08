<?php

namespace JSPSystem\YahooShoppingApiClient\Question;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * ファイル取得API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/question/fileDownload.html
 */
class ExternalTalkFileDownloadClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/externalTalkFileDownload';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/externalTalkFileDownload';

    /**
     * ファイルを取得します。
     *
     * @param array $parameters リクエストパラメータ
     * @return array
     */
    public function request(array $parameters): array
    {
        // パラメーターにセラーIDが無ければ例外
        $seller_id = $parameters['sellerId'] ?? null;
        if (empty($seller_id)) {
            throw new ApiException('sellerId not specified in parameter');
        }

        // GETでリクエスト
        return $this->get(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $parameters
        );
    }

    /**
     * レスポンスからContent-Typeとbodyへパースします。
     *
     * @return array
     */
    protected function parseResponseToArray(): array
    {
        // レスポンスが空
        if (!$this->body) {
            throw new ApiException('no response', 'Failed to get the response body');
        }

        // 共通エラーの可能性があるため、レスポンスボディをXML→JSONへ変換を試みる
        $json = $this->convertXmlToJson();
        if (false !== $json) {
            // 変換できた場合は、デコードして共通エラーチェック
            $json_response = json_decode($json, true);
            if (isset($json_response['Message'])) {
                $code  = isset($json_response['Code']) ? $json_response['Code'] : '';
                if (!empty($code)) {
                    $code .= ': ';
                }
                $error = $json_response['Message'];
                throw new ApiException($error, $code . $error);
            }
        }

        // 特に問題なければヘッダからContent-Typeとレスポンスボディを返す
        return [
            'content-type' => $this->headers['content-type'],
            'body'         => $this->body,
        ];
    }

}
