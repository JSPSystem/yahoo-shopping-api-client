<?php

namespace JSPSystem\YahooShoppingApiClient\Question;

use CURLFile;
use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * ファイル投稿API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/question/fileAdd.html
 */
class ExternalTalkFileAddClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/externalTalkFileAdd';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/externalTalkFileAdd';

    /**
     * ファイルを投稿します。
     *
     * @param string $id セラーID
     * @param array $parameters トピックIDとファイル情報。
     * 例) [topicId => '', file => UploadedFile]
     * @return array
     */
    public function request(array $parameters): array
    {
        // パラメーターにトピックID・セラーID・ファイルが無ければ例外
        $topic_id = $parameters['topicId'] ?? null;
        if (empty($topic_id)) {
            throw new ApiException('topicId not specified in parameter');
        }
        $seller_id = $parameters['sellerId'] ?? null;
        if (empty($seller_id)) {
            throw new ApiException('sellerId not specified in parameter');
        }
        $file = $parameters['file'] ?? null;
        if (empty($file)) {
            throw new ApiException('file not specified in parameter');
        }

        // マルチパートでリクエスト
        $url = $this->getUrl($seller_id, self::URL, self::TEST_URL) . '?'
             . "topicId={$topic_id}&sellerId={$seller_id}";
        return $this->asMultipart()->post($url, ['file' => new CURLFile(
            $file->getRealPath(),
            $file->getMimeType(),
            $file->getClientOriginalName()
        )]);
    }

}
