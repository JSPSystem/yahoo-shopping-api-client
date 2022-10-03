<?php

namespace JSPSystem\YahooShoppingApiClient\Stock;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 在庫更新API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/setStock.html
 */
class SetStockClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/setStock';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/setStock';

    /**
     * 在庫情報を更新します。
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

    /**
     * 結果からエラーになった商品を取得します。
     *
     * @param array $result リクエストした結果
     * @return array
     */
    public function getErrorItems(array $result): array
    {
        // 1件のみの場合、例外が発生しない=成功になるのでエラー商品はない
        if (1 == $result['@attributes']['totalResultsReturned']) {
            return [];
        }
        // 複数更新した場合、エラーの商品も含まれるためエラー商品のみ取得
        $items = [];
        foreach ($result['Result'] as $item) {
            if (!isset($item['ErrorCode'])) {
                continue;
            }
            $items[] = $item;
        }
        return $items;
    }

}
