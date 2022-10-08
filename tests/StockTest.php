<?php

namespace JSPSystem\YahooShoppingApiClient\Tests;

use JSPSystem\YahooShoppingApiClient\Stock\GetStockClient;
use JSPSystem\YahooShoppingApiClient\Stock\SetStockClient;

/**
 * Yahoo! Shopping API 在庫関連APIテスト
 */
class StockTest extends AbstractTestCase
{
    /**
     * 在庫更新API・在庫参照APIテスト
     *
     * @return void
     */
    public function testStock(): void
    {
        $access_token = $_ENV['TEST_ACCESS_TOKEN'];
        $seller_id    = $_ENV['TEST_SELLER_ID'];
        $item_code    = $_ENV['TEST_STOCK_ITEM_CODE_OK'];
        $quantity     = $this->faker->randomDigitNotNull;

        // 1商品の在庫を更新
        $set_client = new SetStockClient($access_token);
        $response   = $set_client->request([
            'seller_id' => $seller_id,
            'item_code' => $item_code,
            'quantity'  => $quantity,
        ]);

        // レスポンスが存在するか
        $this->assertNotEmpty($response);
        // 結果が存在するか
        $result = $response['Result'] ?? null;
        $this->assertNotEmpty($result);
        // 結果にある更新後の在庫数は一致するか
        $this->assertSame($quantity, (int)$result['Quantity']);

        // 更新した商品の在庫を取得
        $get_client = new GetStockClient($access_token);
        $response   = $get_client->request([
            'seller_id' => $seller_id,
            'item_code' => $item_code,
        ]);

        // レスポンスが存在するか
        $this->assertNotEmpty($response);
        // 結果が存在するか
        $result = $response['Result'] ?? null;
        $this->assertNotEmpty($result);
        // 在庫数が一致するか
        $this->assertSame($quantity, (int)$result['Quantity']);
    }

    /**
     * 複数商品の在庫更新時に一部失敗した場合のテスト
     *
     * @return void
     */
    public function testSetStockError(): void
    {
        $access_token = $_ENV['TEST_ACCESS_TOKEN'];
        $seller_id    = $_ENV['TEST_SELLER_ID'];
        $item_code_ok = $_ENV['TEST_STOCK_ITEM_CODE_OK'];
        $item_code_ng = $_ENV['TEST_STOCK_ITEM_CODE_NG'];
        $quantity_ok  = $this->faker->randomDigitNotNull;
        $quantity_ng  = $this->faker->word;

        // 2商品更新、一方は数量に文字列を設定
        $set_client = new SetStockClient($access_token);
        $response   = $set_client->request([
            'seller_id' => $seller_id,
            'item_code' => $item_code_ok . ',' . $item_code_ng,
            'quantity'  => $quantity_ok . ',' . $quantity_ng,
        ]);

        // レスポンスが存在するか
        $this->assertFalse(empty($response));
        // 結果が存在するか、また複数か
        $result = $response['Result'] ?? null;
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        if (!is_array($result)) {
            // 配列以外の場合は以降のテストは行わない
            return;
        }

        // 総件数は2件か
        $this->assertSame(2, (int)$response['@attributes']['totalResultsAvailable']);
        // 結果を確認
        foreach ($result as $value) {
            $item_code = $value['ItemCode'];
            if (!empty($value['SubCode'])) {
                $item_code .= ':' . $value['SubCode'];
            }

            switch ($item_code) {
                case $item_code_ok:
                    // 更新成功：結果にある更新後の在庫数と一致するか
                    $this->assertSame($quantity_ok, (int)$value['Quantity']);
                    break;
                case $item_code_ng:
                    // 更新失敗：quantityが不正エラーが出ているか
                    $this->assertSame('st-02104', $value['ErrorCode']);
                    break;
            }
        }

        // 結果からエラーのみ取得
        $errors = $set_client->getErrorItems($response);
        // 1件のみか
        $this->assertSame(1, count($errors));
    }

}
