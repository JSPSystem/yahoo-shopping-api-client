<?php

namespace JSPSystem\YahooShoppingApiClient\Item;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 商品参照API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/getItem.html
 */
class GetItemClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/getItem';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/getItem';

    /**
     * パラメータ expand_spec の値。商品のスペックをSpec1～10の固定で返す
     * 
     * @var int
     */
    const EXPAND_SPEC_FIXED = 0;

    /**
     * パラメータ expand_spec の値。商品のスペックをSpecで複数返す
     * 
     * @var int
     */
    const EXPAND_SPEC_ARRAY = 1;

    /**
     * 商品情報を取得します。
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
        $response = $this->get(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $parameters
        );
        // 結果は指定の1商品のみのため結果部分のみ返す、結果がなければ空配列
        return $response['Result'] ?? [];
    }

}
