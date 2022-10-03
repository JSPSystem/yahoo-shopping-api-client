<?php

namespace JSPSystem\YahooShoppingApiClient\Item;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 商品登録API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/editItem.html
 */
class EditItemClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/editItem';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/editItem';

    /**
     * 商品の登録・更新を行います。
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
        $result = $this->asForm()->post(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $parameters
        );
        return $result['Result'];
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
     * 結果に含まれる警告を取得します。
     *
     * @param array $result リクエストした結果
     * @return array
     */
    public function getWarnings(array $result): array
    {
        return isset($result['Warning']) ? $result['Warning'] : [];
    }

    /**
     * 結果に含まれるエラーを取得します。
     *
     * @param array $result リクエストした結果
     * @return array
     */
    public function getErrors(array $result): array
    {
        return isset($result['Error']) ? $result['Error'] : [];
    }

}
