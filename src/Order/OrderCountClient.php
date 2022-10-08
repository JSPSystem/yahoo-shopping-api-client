<?php

namespace JSPSystem\YahooShoppingApiClient\Order;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 注文ステータス別件数参照API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/orderCount.html
 */
class OrderCountClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderCount';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderCount';

    /**
     * 注文ステータス別の件数を取得します。
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
        $response = $this->get(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $parameters
        );
        // 失敗した場合は空配列を返す
        return $this->isSuccess($response) ? $response['Result']['Count'] : [];
    }

    /**
     * 成功したか調べます。
     *
     * @param array $response
     * @return boolean
     */
    private function isSuccess(array $response): bool
    {
        // Resultがなければ失敗
        $result = $response['Result'] ?? null;
        if (empty($result)) {
            return false;
        }
        // StatusがOK以外なら失敗
        if (!isset($result['Status']) || 'OK' !== $result['Status']) {
            return false;
        }
        // Countがなければ失敗
        if (!isset($result['Count'])) {
            return false;
        }
        return true;
    }

}
