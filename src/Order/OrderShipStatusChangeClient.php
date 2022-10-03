<?php

namespace JSPSystem\YahooShoppingApiClient\Order;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 出荷ステータス変更API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/orderShipStatusChange.html
 */
class OrderShipStatusChangeClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderShipStatusChange';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderShipStatusChange';

    /**
     * 出荷ステータスを変更します。
     *
     * @param array $parameters Req以降のリクエストパラメータ。
     * 例)
     * [
     *     'Target' => [
     *         'OrderId'     => 'vr8ffeqerqweqe9d8',
     *         'IsPointFix'  => 'false',
     *     ],
     *     'Order' => [
     *         'Ship' => ['ShipStataus' => 1],
     *     ],
     *     'SellerId' => 'abcd-efg',
     * ];
     * @return array
     */
    public function request(array $parameters): array
    {
        // パラメータにセラーIDが無ければ例外
        $seller_id = $parameters['SellerId'] ?? null;
        if (empty($seller_id)) {
            throw new ApiException('SellerId not specified in parameter');
        }

        // パラメーターからXMLを作成
        $xml = $this->convertArrayToXml('<Req></Req>', $parameters);
        // POSTでリクエスト
        return $this->asForm()->post(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $xml->asXML()
        );
    }

    /**
     * 結果に含まれる警告を取得します。
     *
     * @param array $result リクエストした結果
     * @return array
     */
    public function getWarnings(array $result): array
    {
        return $result['Result']['Warning'] ?? [];
    }

}
