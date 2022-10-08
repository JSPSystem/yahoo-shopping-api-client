<?php

namespace JSPSystem\YahooShoppingApiClient\Order;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;

/**
 * 注文詳細API
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/orderInfo.html
 */
class OrderInfoClient extends BaseApiClient
{
    /**
     * XMLのFieldのデフォルト。全ての項目。
     * 
     * @var string
     */
    private const FIELD = 'OrderId,Version,ParentOrderId,ChildOrderId,DeviceType,'
                        . 'MobileCarrierName,IsSeen,IsSplit,CancelReason,'
                        . 'CancelReasonDetail,IsRoyalty,IsRoyaltyFix,IsSeller,'
                        . 'IsAffiliate,IsRatingB2s,NeedSnl,OrderTime,LastUpdateTime,'
                        . 'Suspect,SuspectMessage,OrderStatus,StoreStatus,'
                        . 'RoyaltyFixTime,SendConfirmTime,SendPayTime,PrintSlipTime,'
                        . 'PrintDeliveryTime,PrintBillTime,BuyerComments,SellerComments,'
                        . 'Notes,OperationUser,Referer,EntryPoint,HistoryId,UsageId,'
                        . 'UseCouponData,TotalCouponDiscount,ShippingCouponFlg,'
                        . 'ShippingCouponDiscount,CampaignPoints,IsMultiShip,'
                        . 'MultiShipId,IsReadOnly,IsFirstClassDrugIncludes,'
                        . 'IsFirstClassDrugAgreement,IsWelcomeGiftIncludes,'
                        . 'YamatoCoopStatus,FraudHoldStatus,PublicationTime,'
                        . 'IsYahooAuctionOrder,YahooAuctionMerchantId,YahooAuctionId,'
                        . 'IsYahooAuctionDeferred,YahooAuctionCategoryType,'
                        . 'YahooAuctionBidType,UseGiftCardData,PayStatus,SettleStatus,'
                        . 'PayType,PayKind,PayMethod,PayMethodName,SellerHandlingCharge,'
                        . 'PayActionTime,PayDate,PayNotes,SettleId,CardBrand,CardNumber,'
                        . 'CardNumberLast4,CardExpireYear,CardExpireMonth,CardPayType,'
                        . 'CardHolderName,CardPayCount,CardBirthDay,UseYahooCard,'
                        . 'UseWallet,NeedBillSlip,NeedDetailedSlip,NeedReceipt,'
                        . 'AgeConfirmField,AgeConfirmValue,AgeConfirmCheck,'
                        . 'BillAddressFrom,BillFirstName,BillFirstNameKana,BillLastName,'
                        . 'BillLastNameKana,BillZipCode,BillPrefecture,'
                        . 'BillPrefectureKana,BillCity,BillCityKana,BillAddress1,'
                        . 'BillAddress1Kana,BillAddress2,BillAddress2Kana,'
                        . 'BillPhoneNumber,BillEmgPhoneNumber,BillMailAddress,'
                        . 'BillSection1Field,BillSection1Value,BillSection2Field,'
                        . 'BillSection2Value,PayNo,PayNoIssueDate,ConfirmNumber,'
                        . 'PaymentTerm,IsApplePay,ShipStatus,ShipMethod,ShipMethodName,'
                        . 'ShipRequestDate,ShipRequestTime,ShipNotes,ShipCompanyCode,'
                        . 'ReceiveShopCode,ShipInvoiceNumber1,ShipInvoiceNumber2,'
                        . 'ShipInvoiceNumberEmptyReason,ShipUrl,ArriveType,ShipDate,'
                        . 'ArrivalDate,NeedGiftWrap,GiftWrapCode,GiftWrapType,'
                        . 'GiftWrapMessage,NeedGiftWrapPaper,GiftWrapPaperType,'
                        . 'GiftWrapName,Option1Field,Option1Type,Option1Value,'
                        . 'Option2Field,Option2Type,Option2Value,ShipFirstName,'
                        . 'ShipFirstNameKana,ShipLastName,ShipLastNameKana,ShipZipCode,'
                        . 'ShipPrefecture,ShipPrefectureKana,ShipCity,ShipCityKana,'
                        . 'ShipAddress1,ShipAddress1Kana,ShipAddress2,ShipAddress2Kana,'
                        . 'ShipPhoneNumber,ShipEmgPhoneNumber,ShipSection1Field,'
                        . 'ShipSection1Value,ShipSection2Field,ShipSection2Value,'
                        . 'ReceiveSatelliteType,ReceiveSatelliteSettleMethod,'
                        . 'ReceiveSatelliteMethod,ReceiveSatelliteCompanyName,'
                        . 'ReceiveSatelliteShopCode,ReceiveSatelliteShopName,'
                        . 'ReceiveSatelliteShipKind,ReceiveSatelliteYahooCode,'
                        . 'ReceiveSatelliteCertificationNumber,CollectionDate,'
                        . 'CashOnDeliveryTax,NumberUnitsShipped,ShipRequestTimeZoneCode,'
                        . 'ShipInstructType,ShipInstructStatus,ReceiveShopType,'
                        . 'ReceiveShopName,ExcellentDelivery,IsEazy,EazyDeliveryCode,'
                        . 'EazyDeliveryName,PayCharge,ShipCharge,GiftWrapCharge,'
                        . 'Discount,Adjustments,SettleAmount,UsePoint,GiftCardDiscount,'
                        . 'TotalPrice,SettlePayAmount,IsGetPointFixAll,'
                        . 'TotalMallCouponDiscount,IsGetStoreBonusFixAll,LineId,ItemId,'
                        . 'Title,SubCode,SubCodeOption,ItemOption,Inscription,IsUsed,'
                        . 'ImageId,IsTaxable,ItemTaxRatio,Jan,ProductId,CategoryId,'
                        . 'AffiliateRatio,UnitPrice,Quantity,PointAvailQuantity,'
                        . 'ReleaseDate,PointFspCode,PointRatioY,PointRatioSeller,'
                        . 'UnitGetPoint,IsGetPointFix,GetPointFixDate,CouponData,'
                        . 'CouponDiscount,CouponUseNum,OriginalPrice,OriginalNum,'
                        . 'LeadTimeText,LeadTimeStart,LeadTimeEnd,PriceType,'
                        . 'PickAndDeliveryCode,PickAndDeliveryTransportRuleType,'
                        . 'YamatoUndeliverableReason,StoreBonusRatioSeller,'
                        . 'UnitGetStoreBonus,IsGetStoreBonusFix,GetStoreBonusFixDate,'
                        . 'SellerId,IsLogin,GuestAuthId';

    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderInfo';

    /**
     * テスト環境URL
     * 
     * @var string
     */
    const TEST_URL = 'https://test.circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderInfo';

    /**
     * 注文の詳細を取得します。
     * 
     * @param array $parameters Req以降のリクエストパラメータ。
     * Fieldが未設定なら全ての項目名が設定される。
     * 例)
     * [
     *     'Target' => [
     *         'OrderId' => 's4fr3e04848dqw5',
     *         'Field'   => 'OrderId,Version,ParentOrderId,ChildOrderId,…項目…',
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
        $parameters['Target']['Field'] = $parameters['Target']['Field'] ?? self::FIELD;

        // パラメータからXMLを作成
        $xml = $this->convertArrayToXml('<Req></Req>', $parameters);
        // POSTでリクエスト
        $result = $this->asForm()->post(
            $this->getUrl($seller_id, self::URL, self::TEST_URL),
            $xml->asXML()
        );
        // 結果は指定の1注文のみのため結果部分のみ返す、結果がなければ空配列
        return $result['Result']['OrderInfo'] ?? [];
    }

}
