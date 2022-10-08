<?php

namespace JSPSystem\YahooShoppingApiClient\Tests;

use DateTime;
use DateTimeZone;
use JSPSystem\YahooShoppingApiClient\Selling\PublishHistorySummaryClient;
use JSPSystem\YahooShoppingApiClient\Selling\ReservePublishClient;

/**
 * Yahoo! Shopping API 出品管理関連APIテスト
 * 未反映項目がある状態からテスト
 */
class SellingTest extends AbstractTestCase
{
    /**
     * 反映履歴/未反映項目一覧APIテスト 未反映項目
     *
     * @return void
     */
    public function testNotReflected(): void
    {
        $client   = new PublishHistorySummaryClient($_ENV['TEST_ACCESS_TOKEN']);
        $response = $client->request([
            'seller_id'  => $_ENV['TEST_SELLER_ID'],
            'publish_id' => 0,
        ]);

        // 取得件数は１件か
        $this->assertSame(1, (int)$response['@attributes']['totalResultsReturned']);
        // 結果が存在するか
        $this->assertTrue(isset($response['Result']));
    }

    /**
     * 全反映予約APIテスト 反映予約・予約確認
     *
     * @return void
     */
    public function testReserve(): void
    {
        $reserve_time = new DateTime('+2 day', new DateTimeZone('Asia/Tokyo'));

        // 翌々日で反映予約
        $reserve_client = new ReservePublishClient($_ENV['TEST_ACCESS_TOKEN']);
        $result         = $reserve_client->request([
            'seller_id'    => $_ENV['TEST_SELLER_ID'],
            'mode'         => 1,
            'reserve_time' => $reserve_time->format('YmdHi'),
        ]);

        // 予約に成功したか
        $this->assertSame('OK', $result['Status']);
        // 結果にある予約日は一致するか
        $result_time = new DateTime($result['ReserveTime']);
        $this->assertSame(
            $reserve_time->format('YmdHi'),
            $result_time->format('YmdHi')
        );

        // 予約を確認
        $result = $reserve_client->request([
            'seller_id'    => $_ENV['TEST_SELLER_ID'],
            'mode'         => 3,
        ]);

        // 予約確認に成功したか
        $this->assertSame('OK', $result['Status']);
        // 予約日は一致するか
        $result_time = new DateTime($result['ReserveTime']);
        $this->assertSame(
            $reserve_time->format('YmdHi'),
            $result_time->format('YmdHi')
        );
    }

    /**
     * 全反映予約APIテスト 予約取消
     *
     * @return void
     */
    public function testCancel(): void
    {
        // 予約を取消
        $reserve_client = new ReservePublishClient($_ENV['TEST_ACCESS_TOKEN']);
        $result         = $reserve_client->request([
            'seller_id'    => $_ENV['TEST_SELLER_ID'],
            'mode'         => 2,
        ]);

        // 予約取消に成功したか
        $this->assertSame('OK', $result['Status']);

        // 予約を確認
        $result = $reserve_client->request([
            'seller_id'    => $_ENV['TEST_SELLER_ID'],
            'mode'         => 3,
        ]);

        // 予約日は空か
        $this->assertEmpty($result['ReserveTime']);
    }

    /**
     * 全反映予約APIテスト 即時反映
     *
     * @return void
     */
    public function testImmedReflect(): void
    {
        // 即時反映
        $reserve_client = new ReservePublishClient($_ENV['TEST_ACCESS_TOKEN']);
        $result         = $reserve_client->request([
            'seller_id'    => $_ENV['TEST_SELLER_ID'],
            'mode'         => 1,
        ]);

        // 反映に成功したか
        $this->assertSame('OK', $result['Status']);
    }

}
