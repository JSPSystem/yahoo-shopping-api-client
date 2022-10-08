<?php

namespace JSPSystem\YahooShoppingApiClient\Item;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 商品一括更新API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/updateItems.html
 */
class UpdateItemsClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/updateItems';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/updateItems';

    /**
     * 商品の一括更新を行います。
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
     * 結果から成功したか調べます。
     *
     * @param array $result リクエストした結果
     * @return boolean
     */
    public function isSuccess(array $result): bool
    {
        return isset($result['Status']) && 'OK' === $result['Status'];
    }

    /**
     * 結果に含まれるエラーを取得します。
     *
     * @param array $result リクエストした結果
     * @return array
     */
    public function getErrors(array $result): array
    {
        if (!isset($result['Result'])) {
            return [];
        }

        // エラー商品が1件の場合は、複数件の時のように配列に入れて返す
        if (!isset($result['Result'][0])) {
            return [$result['Result']];
        }
        return $result['Result'];
    }

}