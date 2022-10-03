<?php

namespace JSPSystem\YahooShoppingApiClient\Item;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 商品個別反映API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/submitItem.html
 */
class SubmitItemClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/submitItem';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/submitItem';

    /**
     * 指定商品を反映します。
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
        // POSTでリクエスト
        return $this->asForm()->post(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $parameters
        );
    }

}
