<?php

namespace JSPSystem\YahooShoppingApiClient\Shopping;

use JSPSystem\YahooShoppingApiClient\BaseApiClient;

/**
 * 商品検索API（v3）
 * 
 * @link https://developer.yahoo.co.jp/webapi/shopping/shopping/v3/itemsearch.html
 */
class ItemSearchClient extends BaseApiClient
{
    /**
     * 一度に取得する件数のデフォルト
     * 
     * @var int
     */
    private const RESULTS = 20;

    /**
     * 本番環境URL
     * 
     * @var string
     */
    const URL = 'https://shopping.yahooapis.jp/ShoppingWebService/V3/itemSearch';

    /**
     * 商品検索を行います。
     *
     * @param array $parameters リクエストパラメータ
     * @return array
     */
    public function request(array $parameters): array
    {
        // GETでリクエスト。ページングなどのため結果をそのまま返す
        return $this->get(self::URL, $parameters);
    }

    /**
     * 商品検索を行い、全ての商品を取得します。
     *
     * @param array $parameters リクエストパラメータ
     * @return array
     */
    public function requestAll(array $parameters): array
    {
        // resultsがなければデフォルトを設定
        $parameters['results'] = $parameters['results'] ?? self::RESULTS;

        // 全商品取得
        $products = [];
        $start    = 0;
        $total    = 0;
        do {
            // パラメーターにstartを設定してリクエスト
            $parameters['start'] = $start + 1;
            $response = $this->request($parameters);

            // 全件数を取得
            $total = $response['totalResultsAvailable'];
            if (!$total) {
                break;
            }
            // 取得した商品情報を配列へ追加
            $products = array_merge($products, $response['hits']);
            // 次の開始位置を設定
            $start += $parameters['results'];

        } while ($start < $total);

        return $products;
    }

    /**
     * 商品情報から商品コードのみの一覧を取得します。
     *
     * @param array $products
     * @return void
     */
    public function getItemCodes(array $products)
    {
        $list = [];
        foreach ($products as $product) {
            // アンダーバーで分割（seller_id・item_codeにアンダーバーは使用できない）
            $code   = explode('_', $product['code'], 2);
            $list[] = $code[1];
        }
        return $list;
    }

}
