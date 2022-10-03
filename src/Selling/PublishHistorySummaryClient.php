<?php

namespace JSPSystem\YahooShoppingApiClient\Selling;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 反映履歴/未反映項目一覧API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/publishHistorySummary.html
 */
class PublishHistorySummaryClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/publishHistorySummary';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/publishHistorySummary';

    /**
     * 反映した履歴の一覧および未反映項目の一覧を取得します。
     *
     * @param array $parameters リクエストパラメータ
     * @return array
     */
    public function request(array $parameters): array
    {
        // パラメーターにセラーIDが無ければ例外
        $seller_id = $parameters['seller_id'] ?? null;
        if (empty($seller_id)) {
            throw new ApiException('seller_id not specified in parameter');
        }
        // GETでリクエスト
        return $this->get(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $parameters
        );
    }

}
