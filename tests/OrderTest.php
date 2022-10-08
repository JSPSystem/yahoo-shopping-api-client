<?php

namespace JSPSystem\YahooShoppingApiClient\Tests;

use DateTime;
use JSPSystem\YahooShoppingApiClient\Exception\ApiException;
use JSPSystem\YahooShoppingApiClient\Order\OrderChangeClient;
use JSPSystem\YahooShoppingApiClient\Order\OrderCountClient;
use JSPSystem\YahooShoppingApiClient\Order\OrderInfoClient;
use JSPSystem\YahooShoppingApiClient\Order\OrderItemAddClient;
use JSPSystem\YahooShoppingApiClient\Order\OrderListClient;
use JSPSystem\YahooShoppingApiClient\Order\OrderPayStatusChangeClient;
use JSPSystem\YahooShoppingApiClient\Order\OrderShipStatusChangeClient;
use JSPSystem\YahooShoppingApiClient\Order\OrderStatusChangeClient;

/**
 * Yahoo! Shopping API 注文関連APIテスト
 * 新規予約、新規注文が1件ずつで商品を1つ注文している状態からテスト
 */
class OrderTest extends AbstractTestCase
{

    /**
     * 注文ステータス別件数参照APIテスト
     *
     * @return void
     */
    public function testOrderCount(): void
    {
        $client = new OrderCountClient($_ENV['TEST_ACCESS_TOKEN']);
        $count  = $client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
        ]);

        // 成功したか
        $this->assertNotEmpty($count);
        // 件数は正しいか
        $this->assertSame(1, (int)$count['NewOrder']);      // 新規注文
        $this->assertSame(1, (int)$count['NewReserve']);    // 新規予約
        $this->assertSame(0, (int)$count['Holding']);       // 保留
    }

    /**
     * 注文検索APIテスト
     *
     * @return void
     */
    public function testOrderList(): void
    {
        $client = new OrderListClient($_ENV['TEST_ACCESS_TOKEN']);

        // 新規予約の注文IDで検索
        $response = $client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Search'   => [
                'Condition' => [
                    'OrderId' => $_ENV['TEST_NEW_RESERVE_ID'],
                ],
            ]
        ]);

        // 成功か
        $this->assertSame('OK', $response['Status']);
        // 件数は1件か
        $this->assertSame(1, (int)$response['Search']['TotalCount']);
        // 注文情報は存在するか
        $order = $response['Search']['OrderInfo'] ?? null;
        $this->assertNotEmpty($order);
        // 新規予約か（IsSeen=false and OrderStatus=1:予約中）
        $this->assertSame('false', $order['IsSeen']);
        $this->assertSame(1, (int)$order['OrderStatus']);

        // 新規注文の注文を検索（IsSeen=false and OrderStatus=2:処理中）
        $response = $client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Search'   => [
                'Condition' => [
                    'OrderTimeFrom' => '20221001000000',
                    'OrderTimeTo'   => '20221031235959',
                    'IsSeen'        => 'false',
                    'OrderStatus'   => 2,
                ],
            ]
        ]);
        
        // 成功か
        $this->assertSame('OK', $response['Status']);
        // 件数は1件か
        $this->assertSame(1, (int)$response['Search']['TotalCount']);
        // 注文情報は存在するか
        $order = $response['Search']['OrderInfo'] ?? null;
        $this->assertNotEmpty($order);
        // 注文IDは一致するか
        $this->assertSame($_ENV['TEST_NEW_ORDER_ID'], $order['OrderId']);
    }

    /**
     * 注文詳細APIテスト
     *
     * @return void
     */
    public function testOrderInfo(): void
    {
        $client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);

        // 新規予約の注文を取得
        $order = $client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => [
                'OrderId' => $_ENV['TEST_NEW_RESERVE_ID'],
            ]
        ]);

        // 注文情報は存在するか
        $this->assertNotEmpty($order);
        // 新規予約になっているか（IsSeen=false and OrderStatus=1:予約中）
        $this->assertSame('false', $order['IsSeen']);
        $this->assertSame(1, (int)$order['OrderStatus']);
        // 商品が存在するか
        $item = $order['Item'] ?? null;
        $this->assertNotEmpty($item);
        // 明細の商品コードが一致するか
        $this->assertSame($_ENV['TEST_NEW_RESERVE_ITEM_CODE'], $item['ItemId']);

        // 新規注文の注文を取得
        $order = $client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => [
                'OrderId' => $_ENV['TEST_NEW_ORDER_ID'],
            ]
        ]);

        // 注文情報は存在するか
        $this->assertNotEmpty($order);
        // 新規注文になっているか（IsSeen=false and OrderStatus=2:処理中）
        $this->assertSame('false', $order['IsSeen']);
        $this->assertSame(2, (int)$order['OrderStatus']);
        // 商品が存在するか
        $item = $order['Item'] ?? null;
        $this->assertNotEmpty($item);
        // 明細の商品コードが一致するか
        $this->assertSame($_ENV['TEST_NEW_ORDER_ITEM_CODE'], $item['ItemId']);
    }

    /**
     * 注文内容変更APIテスト 新規予約
     *
     * @return void
     */
    public function testOrderChangeNewReserve(): void
    {
        $first_name = $this->faker->firstName;
        $last_name  = $this->faker->lastName;
        $quantity   = $this->faker->randomDigitNotNull;

        // 新規予約の注文の届け先名と明細1行目の数量を更新
        $change_client = new OrderChangeClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $change_client->request([
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_RESERVE_ID']],
            'Order'    => [
                'Ship' => ['ShipFirstName' => $first_name, 'ShipLastName' => $last_name],
                'Item' => ['LineId' => 1, 'Quantity' => $quantity],
            ],
            'SellerId' => $_ENV['TEST_SELLER_ID'],
        ]);

        // 更新に成功したか
        $this->assertTrue($change_client->isSuccess($result));
        // 更新時に警告がないか
        $this->assertEmpty($change_client->getWarnings($result));

        // 更新後の注文を取得
        $info_client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);
        $order       = $info_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_RESERVE_ID']]
        ]);

        // ステータスが予約になっているか（IsSeen=true and OrderStatus=1:予約中）
        $this->assertSame('true', $order['IsSeen']);
        $this->assertSame(1, (int)$order['OrderStatus']);
        // お届け先が更新されているか
        $this->assertSame($first_name, $order['Ship']['ShipFirstName']);
        $this->assertSame($last_name, $order['Ship']['ShipLastName']);
        // 商品の数量は更新されているか
        $this->assertSame($quantity, (int)$order['Item']['Quantity']);
    }

    /**
     * 注文内容変更APIテスト 新規注文
     *
     * @return void
     */
    public function testOrderChangeNewOrder(): void
    {
        $first_name = $this->faker->firstName;
        $last_name  = $this->faker->lastName;
        $quantity   = $this->faker->randomDigitNotNull;

        // 新規注文の注文の届け先名と明細1行目の数量を更新
        $change_client = new OrderChangeClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $change_client->request([
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_ORDER_ID']],
            'Order'    => [
                'Ship' => ['ShipFirstName' => $first_name, 'ShipLastName' => $last_name],
                'Item' => ['LineId' => 1, 'Quantity' => $quantity],
            ],
            'SellerId' => $_ENV['TEST_SELLER_ID'],
        ]);

        // 更新に成功したか
        $this->assertTrue($change_client->isSuccess($result));
        // 更新時に警告がないか
        $this->assertEmpty($change_client->getWarnings($result));

        // 更新後の注文を取得
        $info_client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);
        $order       = $info_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_ORDER_ID']]
        ]);

        // ステータスが処理中になっているか（IsSeen=true and OrderStatus=2:処理中）
        $this->assertSame('true', $order['IsSeen']);
        $this->assertSame(2, (int)$order['OrderStatus']);
        // お届け先が更新されているか
        $this->assertSame($first_name, $order['Ship']['ShipFirstName']);
        $this->assertSame($last_name, $order['Ship']['ShipLastName']);
        // 商品の数量は更新されているか
        $this->assertSame($quantity, (int)$order['Item']['Quantity']);
    }

    /**
     * 注文ライン追加APIテスト
     *
     * @return void
     */
    public function testOrderItemAdd(): void
    {
        $line_id  = 2;
        $quantity = $this->faker->randomDigitNotNull;

        // 明細追加
        $add_client = new OrderItemAddClient($_ENV['TEST_ACCESS_TOKEN']);
        $result     = $add_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => [
                'OrderId'     => $_ENV['TEST_NEW_ORDER_ID'],
                'IsQuotation' => 'true',
            ],
            'Order'    => [
                'Item' => [
                    [
                        'LineId'   => $line_id,
                        'ItemId'   => $_ENV['TEST_NEW_LINE_ITEM_CODE'],
                        'SubCode'  => $_ENV['TEST_NEW_LINE_SUB_CODE'],
                        'ItemOption' => [
                            [
                                'Index' => 1,
                                'Name'  => $_ENV['TEST_NEW_LINE_OPTION_NAME_1'],
                                'Value' => $_ENV['TEST_NEW_LINE_OPTION_VALUE_1'],
                            ],
                            [
                                'Index' => 2,
                                'Name'  => $_ENV['TEST_NEW_LINE_OPTION_NAME_2'],
                                'Value' => $_ENV['TEST_NEW_LINE_OPTION_VALUE_2'],
                            ],
                        ],
                        'Quantity' => $quantity,
                    ],
                ],
            ],
        ]);

        // 更新に成功したか
        $this->assertTrue($add_client->isSuccess($result));
        // 更新時に警告がないか
        $this->assertEmpty($add_client->getWarnings($result));

        // 更新後の注文を取得
        $info_client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);
        $order       = $info_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_ORDER_ID']]
        ]);

        // 商品が存在するか
        $items = $order['Item'] ?? null;
        $this->assertNotEmpty($items);
        // 明細が複数あるか
        $this->assertTrue(isset($items[1]));
        // 追加した商品が存在するか
        $add_item = [];
        foreach ($items as $item) {
            if ($item['LineId'] != $line_id) {
                continue;
            }
            $add_item = $item;
        }
        $this->assertNotEmpty($add_item);
        // サブコード・数量が一致するか
        $this->assertSame($_ENV['TEST_NEW_LINE_SUB_CODE'], $item['SubCode']);
        $this->assertSame($quantity, (int)$item['Quantity']);
    }

    /**
     * 入金ステータス変更APIテスト
     *
     * @return void
     */
    public function testOrderPayStatusChange(): void
    {
        $pay_status = 1;
        $pay_date   = new DateTime();

        // 入金済みに変更 ※例外が発生しなければ成功
        $change_client = new OrderPayStatusChangeClient($_ENV['TEST_ACCESS_TOKEN']);
        $change_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_ORDER_ID']],
            'Order'    => [
                'Pay' => [
                    'PayStatus' => $pay_status,
                    'PayDate'   => $pay_date->format('Ymd'),
                ],
            ],
        ]);

        // 更新後の注文を取得
        $info_client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);
        $order       = $info_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_ORDER_ID']]
        ]);

        // 入金ステータスが更新されているか
        $this->assertSame($pay_status, (int)$order['Pay']['PayStatus']);
        // 入金日が更新されているか
        $this->assertSame($pay_date->format('Y-m-d'), $order['Pay']['PayDate']);
    }

    /**
     * 出荷ステータス変更APIテスト
     *
     * @return void
     */
    public function testOrderShipStatusChange(): void
    {
        $is_point_fix   = 'true';
        $ship_status    = 3;
        $ship_date      = new DateTime();
        $invoice_number = '123-456789-012345';

        // 出荷済みに変更 ※例外が発生しなければ成功
        $change_client = new OrderShipStatusChangeClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $change_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => [
                'OrderId'    => $_ENV['TEST_NEW_ORDER_ID'],
                'IsPointFix' => $is_point_fix,
            ],
            'Order'    => [
                'Ship' => [
                    'ShipStatus'         => $ship_status,
                    'ShipInvoiceNumber1' => $invoice_number,
                    'ShipDate'           => $ship_date->format('Ymd'),
                ],
            ],
        ]);

        // 更新時に警告は出てないか
        $this->assertEmpty($change_client->getWarnings($result));

        // 更新後の注文を取得
        $info_client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);
        $order       = $info_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_ORDER_ID']]
        ]);

        // 出荷ステータスが更新されているか
        $this->assertSame($ship_status, (int)$order['Ship']['ShipStatus']);
        // 出荷日が更新されているか
        $this->assertSame($ship_date->format('Y-m-d'), $order['Ship']['ShipDate']);
    }

    /**
     * 注文ステータス変更APIテスト 予約→キャンセル
     *
     * @return void
     */
    public function testOrderStatusChangeReserve(): void
    {
        $is_point_fix = 'false';
        $status       = 4;
        $reason       = 220;

        // 予約中からキャンセルに注文ステータスを変更 ※例外が発生しなければ成功
        $change_client = new OrderStatusChangeClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $change_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => [
                'OrderId'    => $_ENV['TEST_NEW_RESERVE_ID'],
                'IsPointFix' => $is_point_fix,
            ],
            'Order'    => [
                'OrderStatus'        => $status,
                'CancelReason'       => $reason,
            ],
        ]);

        // 更新時に警告は出てないか
        $this->assertEmpty($change_client->getWarnings($result));

        // 更新後の注文を取得
        $info_client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);
        $order       = $info_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_RESERVE_ID']]
        ]);

        // 注文ステータスは更新されているか
        $this->assertSame($status, (int)$order['OrderStatus']);
        // キャンセル理由は更新されているか
        $this->assertSame($reason, (int)$order['CancelReason']);
    }

    /**
     * 注文ステータス変更APIテスト 処理中→完了
     *
     * @return void
     */
    public function testOrderStatusChangeOrder(): void
    {
        $is_point_fix   = 'true';
        $status         = 5;

        // 処理中から完了に注文ステータスを変更 ※例外が発生しなければ成功
        $change_client = new OrderStatusChangeClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $change_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => [
                'OrderId'    => $_ENV['TEST_NEW_ORDER_ID'],
                'IsPointFix' => $is_point_fix,
            ],
            'Order'    => ['OrderStatus' => $status],
        ]);

        // 更新時に警告は出てないか
        $this->assertEmpty($change_client->getWarnings($result));

        // 更新後の注文を取得
        $info_client = new OrderInfoClient($_ENV['TEST_ACCESS_TOKEN']);
        $order       = $info_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => ['OrderId' => $_ENV['TEST_NEW_ORDER_ID']]
        ]);

        // 注文ステータスは更新されているか
        $this->assertSame($status, (int)$order['OrderStatus']);
    }

    /**
     * 注文ステータスの遷移ルールに違反した場合のテスト
     * キャンセルから処理中に変更し、例外が発生するか
     *
     * @return void
     */
    public function testOrderStatusViolation(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Cannot Be Operated : CancelOrder OrderStatus');

        $is_point_fix = 'false';
        $status       = 1;
        
        // キャンセルから予約中に注文ステータスを変更
        $change_client = new OrderStatusChangeClient($_ENV['TEST_ACCESS_TOKEN']);
        $change_client->request([
            'SellerId' => $_ENV['TEST_SELLER_ID'],
            'Target'   => [
                'OrderId'    => $_ENV['TEST_NEW_RESERVE_ID'],
                'IsPointFix' => $is_point_fix,
            ],
            'Order'    => ['OrderStatus' => $status],
        ]);
    }

}
