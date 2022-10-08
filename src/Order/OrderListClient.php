<?php

namespace JSPSystem\YahooShoppingApiClient\Order;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 注文検索API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/orderList.html
 */
class OrderListClient extends BaseApiClient
{
    /**
     * 一度に取得する件数のデフォルト
     * 
     * @var int
     */
    private const RESULT = 10;

    /**
     * XMLのFieldのデフォルト。全ての項目。
     * 
     * @var string
     */
    private const FIELD = 'OrderId,Version,OriginalOrderId,ParentOrderId,DeviceType,'
                        . 'IsSeen,IsSplit,IsRoyalty,IsSeller,IsAffiliate,IsRatingB2s,'
                        . 'OrderTime,ExistMultiReleaseDate,ReleaseDate,LastUpdateTime,'
                        . 'Suspect,OrderStatus,StoreStatus,RoyaltyFixTime,PrintSlipFlag,'
                        . 'PrintDeliveryFlag,PrintBillFlag,BuyerCommentsFlag,PayStatus,'
                        . 'SettleStatus,PayType,PayMethod,PayMethodName,PayDate,SettleId,'
                        . 'UseWallet,NeedBillSlip,NeedDetailedSlip,NeedReceipt,'
                        . 'BillFirstName,BillFirstNameKana,BillLastName,'
                        . 'BillLastNameKana,BillPrefecture,ShipStatus,ShipMethod,'
                        . 'ShipMethodName,ShipRequestDate,ShipRequestTime,ShipNotes,'
                        . 'ShipCompanyCode,ReceiveShopCode,ShipInvoiceNumber1,'
                        . 'ShipInvoiceNumber2,ShipInvoiceNumberEmptyReason,ShipUrl,'
                        . 'ArriveType,ShipDate,NeedGiftWrap,NeedGiftWrapMessage,'
                        . 'NeedGiftWrapPaper,ShipFirstName,ShipFirstNameKana,'
                        . 'ShipLastName,ShipLastNameKana,ShipPrefecture,PayCharge,'
                        . 'ShipCharge,GiftWrapCharge,Discount,GiftCardDiscount,UsePoint,'
                        . 'TotalPrice,RefundTotalPrice,IsGetPointFixAll,SellerId,'
                        . 'IsLogin,PayNo,PayNoIssueDate,SellerType,IsPayManagement,'
                        . 'ArrivalDate,TotalMallCouponDiscount,IsReadOnly,IsApplePay,'
                        . 'IsFirstClassDrugIncludes,IsFirstClassDrugAgreement,'
                        . 'IsWelcomeGiftIncludes,ReceiveSatelliteType,ShipInstructType,'
                        . 'ShipInstructStatus,YamatoCoopStatus,ReceiveShopType,'
                        . 'ReceiveShopName,ExcellentDelivery,IsEazy,EazyDeliveryCode,'
                        . 'EazyDeliveryName,FraudHoldStatus,PublicationTime,'
                        . 'IsYahooAuctionOrder,YahooAuctionMerchantId,YahooAuctionId,'
                        . 'IsYahooAuctionDeferred,YahooAuctionCategoryType,'
                        . 'YahooAuctionBidType';

    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderList';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderList';

    /**
     * 注文検索を行います。
     * 
     * @param array $parameters Req以降のリクエストパラメータ。
     * Fieldが未設定なら全ての項目名が設定される。
     * 例)
     * [
     *     'Search' => [
     *         'Result'    => 10,
     *         'Start'     => 1,
     *         'Sort'      => '+order_time',
     *         'Condition' => [
     *             'OrderTimeFrom' => '20220930000000',
     *             'OrderTimeTo'   => '20221001235959',
     *             …検索条件…,
     *         ],
     *         'Field'     => 'OrderId,IsSeen,OrderTime,OrderStatus,…項目…',
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
        // パラメータにFieldがなければデフォルトを設定
        $parameters['Search']['Field'] = $parameters['Search']['Field'] ?? self::FIELD;

        // パラメータからXMLを作成
        $xml = $this->convertArrayToXml('<Req></Req>', $parameters);
        // POSTでリクエスト
        return $this->asForm()->post(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $xml->asXML()
        );
    }

    /**
     * 注文検索を行い、全ての商品を取得します。
     *
     * @param string $id セラーID
     * @param array $parameters Req以降のリクエストパラメータ。
     * Fieldが未設定なら全ての項目名が設定される。
     * 例)
     * [
     *     'Search' => [
     *         'Result'    => 10,
     *         'Start'     => 1,
     *         'Sort'      => '+order_time',
     *         'Condition' => [
     *             'OrderTimeFrom' => '20220930000000',
     *             'OrderTimeTo'   => '20221001235959',
     *             …検索条件…,
     *         ],
     *         'Field'     => 'OrderId,IsSeen,OrderTime,OrderStatus,…項目…',
     *     ],
     *     'SellerId' => 'abcd-efg',
     * ];
     * @return array
     */
    public function requestAll(array $parameters): array
    {
        // パラメーターにセラーIDが無ければ例外
        $seller_id = $parameters['SellerId'] ?? null;
        if (empty($seller_id)) {
            throw new ApiException('SellerId not specified in parameter');
        }
        // パラメータにField・Resultがなければデフォルトを設定
        $parameters['Search']['Field']  = $parameters['Search']['Field'] ?? self::FIELD;
        $parameters['Search']['Result'] = $parameters['Search']['Result'] ?? self::RESULT;

        // 対象受注を全て取得
        $orders = [];
        $start  = 0;
        $total  = 0;
        do {
            // パラメータにStartを設定してリクエスト
            $parameters['Search']['Start'] = $start + 1;
            $result = $this->request($parameters);
            $search = $result['Search'];

            // 全件数を取得
            $total = $search['TotalCount'];
            if (!$total) {
                break;
            }

            // 注文情報
            $order = $search['OrderInfo'];
            if (isset($order['OrderId'])) {
                // 1件のみの場合は直下に注文情報があるので、複数件と同じ条件にする
                $order = [$search['OrderInfo']];
            }

            // 取得した受注情報を配列へ追加
            $orders = array_merge($orders, $order);
            // 次の開始位置を設定
            $start += $parameters['Search']['Result'];

        } while ($start < $total);

        return $orders;
    }

}
