<?php

namespace JSPSystem\YahooShoppingApiClient\Tests;

use JSPSystem\YahooShoppingApiClient\Shopping\ItemSearchClient;

/**
 * Yahoo! Shopping API ショッピング情報取得APIテスト
 */
class ShoppingTest extends AbstractTestCase
{
    /**
     * 商品検索API（v3）テスト
     *
     * @return void
     */
    public function testItemSearch(): void
    {
        $results = $this->faker->randomDigitNotNull;

        // 商品を検索
        $client = new ItemSearchClient();
        $result = $client->request([
            'appid'   => $_ENV['TEST_APPID'],
            'query'   => $_ENV['TEST_ITEM_SEARCH_QUERY'],
            'results' => $results,
        ]);

        // 結果が存在するか
        $this->assertNotEmpty($result);
        // 検索結果の返却数は一致するか
        $this->assertSame($results, $result['totalResultsReturned']);
        // 実際の結果の数と設定した返却数が一致するか
        $this->assertSame($results, count($result['hits']));
    }

    /**
     * ItemSearchClient::requestAllテスト
     *
     * @return void
     */
    public function testItemSearchAll(): void
    {
        $appid = $_ENV['TEST_APPID'];
        $query = $_ENV['TEST_ITEM_SEARCH_QUERY'];

        // 商品を1件だけ検索し、総検索ヒット件数を取得
        $client = new ItemSearchClient();
        $result = $client->request([
            'appid'   => $appid,
            'query'   => $query,
            'results' => 1,
        ]);
        $total = $result['totalResultsAvailable'];

        // 全件取得
        $items = $client->requestAll([
            'appid'   => $appid,
            'query'   => $query,
            'results' => 50,
        ]);

        // 結果が存在するか
        $this->assertNotEmpty($items);
        // 総検索ヒット数と一致するか
        $this->assertSame($total, count($items));
    }

}
