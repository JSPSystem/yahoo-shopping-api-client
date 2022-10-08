<?php

namespace JSPSystem\YahooShoppingApiClient\Order;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 注文ライン追加API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/orderItemAdd.html
 */
class OrderItemAddClient extends BaseApiClient
{
    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderItemAdd';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderItemAdd';

    /**
     * 注文明細に商品を追加します。
     *
     * @param array $parameters Req以降のリクエストパラメータ。
     * 例)
     * [
     *     'Target' => [
     *         'OrderId'     => 'vr8ffeqerqweqe9d8',
     *         'IsQuotation' => true,
     *     ],
     *     'Order'  => [
     *         'Item' => [
     *             [
     *                 'LineId'   => 1,
     *                 'ItemId'   => 't-shirts',
     *                 'SubCode'  => 't-shirts-red-s'
     *                 'ItemOption' => [
     *                     ['Index' => 1, 'Name' => '色', 'Value' => 'レッド'],
     *                     ['Index' => 2, 'Name' => 'サイズ', 'Value' => 'S'],
     *                 ],
     *                 'Quantity' => 5,
     *             ],
     *             …明細…
     *         ]
     *     ],
     *     'SellerId' => 'abcd-efg',
     * ];
     * @return array
     */
    public function request(array $parameters): array
    {
        // パラメーターにセラーIDが無ければ例外
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
     * 結果から成功したか調べます。
     *
     * @param array $result リクエストした結果
     * @return boolean
     */
    public function isSuccess(array $result): bool
    {
        $status = $result['Result']['Status'] ?? false;
        return !empty($status) && 'OK' === $status;
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
