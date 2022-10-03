<?php

namespace JSPSystem\YahooShoppingApiClient\Question;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 質問非公開API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/question/access.html
 */
class ExternalTalkPrivateClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/externalTalkPrivate';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/externalTalkPrivate';

    /**
     * 問い合わせを非公開にします。
     *
     * @param array $parameters トピックIDを含めたリクエストパラメータ。
     * 例) [topicId => '', 'sellerId' => '']
     * @return array
     */
    public function request(array $parameters): array
    {
        // パラメーターにトピックID・セラーIDが無ければ例外
        $topic_id = $parameters['topicId'] ?? null;
        if (empty($topic_id)) {
            throw new ApiException('topicId not specified in parameter');
        }
        $seller_id = $parameters['sellerId'] ?? null;
        if (empty($seller_id)) {
            throw new ApiException('sellerId not specified in parameter');
        }
        // パラメーターからトピックIDを削除
        unset($parameters['topicId']);

        // PUTでリクエスト
        $url = $this->getUrl($seller_id, self::URL, self::TEST_URL) . '?'
             . "topicId={$topic_id}";
        return $this->put($url, $parameters);
    }

}
