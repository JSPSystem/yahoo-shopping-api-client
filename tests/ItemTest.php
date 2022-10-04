<?php

namespace JSPSystem\YahooShoppingApiClient\Tests;

use JSPSystem\YahooShoppingApiClient\Exception\ApiException;
use JSPSystem\YahooShoppingApiClient\Item\DeleteItemClient;
use JSPSystem\YahooShoppingApiClient\Item\EditItemClient;
use JSPSystem\YahooShoppingApiClient\Item\GetItemClient;
use JSPSystem\YahooShoppingApiClient\Item\MoveItemsClient;
use JSPSystem\YahooShoppingApiClient\Item\MyItemListClient;
use JSPSystem\YahooShoppingApiClient\Item\SubmitItemClient;
use JSPSystem\YahooShoppingApiClient\Item\UpdateItemsClient;

/**
 * Yahoo! Shopping API 商品関連APIテスト
 */
class ItemTest extends AbstractTestCase
{
    /**
     * 登録時商品コード
     */
    private const ITEM_CODE = 'test99999';
    /**
     * 登録時商品名
     */
    private const ITEM_NAME = 'テスト商品99999';
    /**
     * 登録時販売価格
     */
    private const ITEM_PRICE = '12345';
    /**
     * 登録プロダクトカテゴリ 1447：食品 > その他食品
     */
    private const ITEM_PCATE = '1447';

    /**
     * 商品登録APIテスト
     *
     * @return void
     */
    public function testEditItem(): void
    {
        // 個別商品なしの必須項目で登録
        $client = new EditItemClient($_ENV['TEST_ACCESS_TOKEN']);
        $result = $client->request([
            'seller_id'        => $_ENV['TEST_SELLER_ID'],
            'item_code'        => self::ITEM_CODE,
            'path'             => $_ENV['TEST_STORE_CATE'],
            'name'             => self::ITEM_NAME,
            'product_category' => self::ITEM_PCATE,
            'price'            => self::ITEM_PRICE,
        ]);

        // 成功または警告はあるが成功したか
        $this->assertSame('OK', $result['Status']);
    }

    /**
     * 商品参照APIテスト
     * testEditItemで登録した商品を取得
     *
     * @return void
     */
    public function testGetItem(): void
    {
        $client = new GetItemClient($_ENV['TEST_ACCESS_TOKEN']);
        $result = $client->request([
            'seller_id'   => $_ENV['TEST_SELLER_ID'],
            'item_code'   => self::ITEM_CODE,
            'expand_spec' => GetItemClient::EXPAND_SPEC_ARRAY,
        ]);

        // 結果が存在するか
        $this->assertFalse(empty($result));
        // 内容は正しいか
        $this->assertSame($_ENV['TEST_STORE_CATE'], $result['PathList']['Path']);
        $this->assertSame(self::ITEM_NAME, $result['Name']);
        $this->assertSame(self::ITEM_PCATE, $result['ProductCategory']);
        $this->assertSame(self::ITEM_PRICE, $result['Price']);
    }

    /**
     * 商品リストAPIテスト
     * testEditItemで登録した商品を取得
     *
     * @return void
     */
    public function testMyItemList(): void
    {
        $client   = new MyItemListClient($_ENV['TEST_ACCESS_TOKEN']);
        $response = $client->request([
            'seller_id' => $_ENV['TEST_SELLER_ID'],
            'type'      => 'item_code',
            'query'     => self::ITEM_CODE,
        ]);

        // レスポンスが存在するか
        $this->assertFalse(empty($response));
        // 取得件数は1件以上か
        $this->assertGreaterThanOrEqual(1, $response['@attributes']['totalResultsReturned']);
        // 結果が存在するか
        $items = $response['Result'] ?? null;
        $this->assertFalse(empty($items));

        // 返された個数が1件の場合は複数件の時と同じ条件にする
        $items = 1 == $response['@attributes']['totalResultsReturned'] ? [$items] : $items;

        // 登録テストで登録した商品情報が存在するか
        $item_info = null;
        foreach ($items as $item) {
            if (self::ITEM_CODE != $item['ItemCode']) {
                continue;
            }
            $item_info = $item;
        }
        $this->assertFalse(empty($item_info));
        // 内容は正しいか
        $this->assertSame(self::ITEM_NAME, $item_info['Name']);
        $this->assertSame(self::ITEM_PRICE, $item_info['Price']);
    }

    /**
     * 商品個別反映APIテスト
     * testEditItemで登録した商品を個別反映
     *
     * @return void
     */
    public function testSubmitItem(): void
    {
        $client   = new SubmitItemClient($_ENV['TEST_ACCESS_TOKEN']);
        $response = $client->request([
            'seller_id' => $_ENV['TEST_SELLER_ID'],
            'item_code' => $_ENV['TEST_STORE_ITEM_CODE'],
        ]);

        // 結果が存在するか
        $this->assertFalse(empty($response));
        // 成功したか
        $this->assertSame('OK', $response['Status']);
    }

    /**
     * 商品移動APIテスト
     * testEditItemで登録した商品を移動
     *
     * @return void
     */
    public function testMoveItem(): void
    {
        // 移動
        $move_client = new MoveItemsClient($_ENV['TEST_ACCESS_TOKEN']);
        $response    = $move_client->request([
            'seller_id'      => $_ENV['TEST_SELLER_ID'],
            'item_code'      => self::ITEM_CODE,
            'from_stcat_key' => $_ENV['TEST_STORE_CATE_ID'],
            'to_stcat_key'   => $_ENV['TEST_TO_STORE_CATE_ID'],
        ]);

        // 結果が存在するか
        $result = $response['Result'] ?? null;
        $this->assertFalse(empty($result));
        // 成功したか
        $this->assertSame('OK', $result['Status']);

        // 商品参照で取得
        $get_client = new GetItemClient($_ENV['TEST_ACCESS_TOKEN']);
        $result     = $get_client->request([
            'seller_id'   => $_ENV['TEST_SELLER_ID'],
            'item_code'   => self::ITEM_CODE,
        ]);

        // 結果が存在するか
        $this->assertFalse(empty($result));
        // 元のカテゴリに存在しないか
        $this->assertNotSame($_ENV['TEST_STORE_CATE'], $result['PathList']['Path']);
        // 移動先のカテゴリになっているか
        $this->assertSame($_ENV['TEST_TO_STORE_CATE'], $result['PathList']['Path']);
    }

    /**
     * 商品一括更新APIテスト
     * testEditItemで登録した商品を更新
     *
     * @return void
     */
    public function testUpdateItems(): void
    {
        // 更新
        $item_code = self::ITEM_CODE;
        $new_name  = $this->faker->word() . self::ITEM_NAME;
        $enc_name  = rawurlencode($new_name);

        $update_client = new UpdateItemsClient($_ENV['TEST_ACCESS_TOKEN']);
        $response      = $update_client->request([
            'seller_id' => $_ENV['TEST_SELLER_ID'],
            'item1'     => "item_code={$item_code}&name={$enc_name}",
        ]);

        // レスポンスが存在するか
        $this->assertFalse(empty($response));
        // 成功したか
        $this->assertSame('OK', $response['Status']);
         
        // 商品参照で取得
        $get_client = new GetItemClient($_ENV['TEST_ACCESS_TOKEN']);
        $result     = $get_client->request([
            'seller_id'   => $_ENV['TEST_SELLER_ID'],
            'item_code'   => $item_code,
        ]);

        // 結果が存在するか
        $this->assertFalse(empty($result));
        // 元の商品名と違うか
        $this->assertNotSame(self::ITEM_NAME, $result['Name']);
        // 新しい商品名になっているか
        $this->assertSame($new_name, $result['Name']);
    }

    /**
     * 商品削除APIテスト
     * testEditItemで登録した商品を削除
     *
     * @return void
     */
    public function testDeleteItem(): void
    {
        $client   = new DeleteItemClient($_ENV['TEST_ACCESS_TOKEN']);
        $response = $client->request([
            'seller_id' => $_ENV['TEST_SELLER_ID'],
            'item_code' => self::ITEM_CODE,
        ]);

        // レスポンスが存在するか
        $this->assertFalse(empty($response));
        // 結果が存在するか
        $result = $response['Result'] ?? null;
        $this->assertFalse(empty($result));
        // 成功したか
        $this->assertSame('OK', $result['Status']);
    }

    /**
     * testDeleteItemで商品が削除できているかテスト
     * 存在しない商品コードで参照し、例外が発生するか
     *
     * @return void
     */
    public function testItemNotFound(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('指定された商品コードは存在しません。');

        // 商品参照で取得
        $client = new GetItemClient($_ENV['TEST_ACCESS_TOKEN']);
        $client->request([
            'seller_id'   => $_ENV['TEST_SELLER_ID'],
            'item_code'   => self::ITEM_CODE,
        ]);
    }

}
