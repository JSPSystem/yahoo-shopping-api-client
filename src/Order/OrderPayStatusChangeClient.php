<?php

namespace JSPSystem\YahooShoppingApiClient\Order;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 入金ステータス変更API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/orderPayStatusChange.html
 */
class OrderPayStatusChangeClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderPayStatusChange';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderPayStatusChange';

    /**
     * 入金ステータスを変更します。
     *
     * @param array $parameters Req以降のリクエストパラメータ。
     * 例)
     * [
     *     'Target' => [
     *         'OrderId'     => 'vr8ffeqerqweqe9d8',
     *     ],
     *     'Order' => [
     *         'Pay' => ['PayStatus' => 1, 'PayDate' => '20220930'],
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

}
