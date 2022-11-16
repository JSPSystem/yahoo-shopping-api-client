# Yahoo! Shopping API Support Library for PHP
![GitHub](https://img.shields.io/github/license/JSPSystem/yahoo-shopping-api-client)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/JSPSystem/yahoo-shopping-api-client)
![GitHub top language](https://img.shields.io/github/languages/top/JSPSystem/yahoo-shopping-api-client)

A library that supports requests to the Yahoo! Shopping API.

## Table of Contents
- [Requirements](#Requirements)
- [Installation](#Installation)
- [A Simple Example](#A-Simple-Example)
- [Supported API](#Supported-API)
- [License](#License)

## Requirements
- PHP ^8.0 (curl and json related packages are required.)
- curl >= 7.52.1
- openssl >= 1.1.0

## Installation
```
composer require jspsystem/yahoo-shopping-api-client
```

## A Simple Example
```php
<?php

use JSPSystem\YahooShoppingApiClient\Item\GetItemClient;

$client = new GetItemClient('Set the acquired access token.');
$result = $client->request([
    'seller_id' => 'seller id',
    'item_code' => 'item code',
]);
```

## Supported API
| Category | API | Namespace | Class |
| --- | --- | --- | --- |
| 商品に関連するAPI | 商品削除API | JSPSystem\YahooShoppingApiClient\Item | DeleteItemClient |
|     | 商品登録API |     | EditItemClient |
|     | 商品参照API |     | GetItemClient |
|     | 商品移動API |     | MoveItemsClient |
|     | 商品リストAPI |     | MyItemListClient |
|     | 商品個別反映API |     | SubmitItemClient |
|     | 商品一括更新API |     | UpdateItemsClient |
| 注文に関するAPI | 注文内容変更API | JSPSystem\YahooShoppingApiClient\Order | OrderChangeClient |
|     | 注文ステータス別件数参照API |     | OrderCountClient |
|     | 注文詳細API |     | OrderInfoClient |
|     | 注文ライン追加API |     | OrderItemAddClient |
|     | 注文検索API |     | OrderListClient |
|     | 入金ステータス変更API |     | OrderPayStatusChangeClient |
|     | 出荷ステータス変更API |     | OrderShipStatusChangeClient |
|     | 注文ステータス変更API |     | OrderStatusChangeClient |
| 問い合わせに関するAPI | メッセージ投稿API | JSPSystem\YahooShoppingApiClient\Question | ExternalTalkAddClient |
|     | 質問完了API |     | ExternalTalkCompleteClient |
|     | 質問詳細API |     | ExternalTalkDetailClient |
|     | ファイル投稿API |     | ExternalTalkFileAddClient |
|     | ファイル取得API |     | ExternalTalkFileDownloadClient |
|     | 質問一覧API |     | ExternalTalkListClient |
|     | 質問非公開API |     | ExternalTalkPrivateClient |
|     | 既読API |     | ExternalTalkReadClient |
| 出品管理に関連するAPI | 反映履歴/未反映項目一覧API | JSPSystem\YahooShoppingApiClient\Selling | PublishHistorySummaryClient |
|     | 全反映予約API |     | ReservePublishClient |
| ショッピング情報取得API | 商品検索API（v3） | JSPSystem\YahooShoppingApiClient\Shopping | ItemSearchClient |
| 在庫に関連するAPI | 在庫参照API | JSPSystem\YahooShoppingApiClient\Stock | GetStockClient |
|     | 在庫更新API |     | SetStockClient |

## License
Yahoo! Shopping API Support Library for PHP is released under the MIT License.  
See the bundled LICENSE file for details.
