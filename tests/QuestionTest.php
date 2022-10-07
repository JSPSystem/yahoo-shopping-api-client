<?php

namespace JSPSystem\YahooShoppingApiClient\Tests;

use CURLFile;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkAddClient;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkCompleteClient;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkDetailClient;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkFileAddClient;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkFileDownloadClient;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkListClient;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkPrivateClient;
use JSPSystem\YahooShoppingApiClient\Question\ExternalTalkReadClient;

/**
 * Yahoo! Shopping API お問い合わせ関連APIテスト
 * ユーザーから、ファイル添付ありの新規問い合わせが存在する状態からテスト
 */
class QuestionTest extends AbstractTestCase
{
    /**
     * 質問一覧APIテスト
     *
     * @return void
     */
    public function testExternalTalkList(): void
    {
        $client = new ExternalTalkListClient($_ENV['TEST_ACCESS_TOKEN']);
        $result = $client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
        ]);

        // 結果が存在するか
        $this->assertFalse(empty($result));
        // 一覧が存在するか
        $headlines = $result['headlines'] ?? null;
        $this->assertFalse(empty($headlines));
        // 指定の問い合わせが存在するか
        $topic = null;
        foreach ($headlines as $headline) {
            if ($headline['topicId'] != $_ENV['TEST_TOPIC_ID']) {
                continue;
            }
            $topic = $headline;
        }
        $this->assertFalse(empty($topic));
    }

    /**
     * 質問詳細APIテスト
     *
     * @return void
     */
    public function testExternalTalkDetail(): void
    {
        $client = new ExternalTalkDetailClient($_ENV['TEST_ACCESS_TOKEN']);
        $result = $client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
        ]);

        // 結果が存在するか
        $this->assertFalse(empty($result));
        // 問い合わせ情報が存在するか
        $topic = $result['topic'] ?? null;
        $this->assertFalse(empty($topic));
        // タイトルが存在するか
        $this->assertFalse(empty($topic['title']));
        // メッセージが存在するか
        $this->assertFalse(empty($result['messages']));
    }

    /**
     * ファイル取得APIテスト
     *
     * @return void
     */
    public function testExternalTalkFileDownload(): void
    {
        // 問い合わせ取得
        $detail_client = new ExternalTalkDetailClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $detail_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
        ]);

        // メッセージに含まれるファイルを受信できるか
        $dl_client = new ExternalTalkFileDownloadClient($_ENV['TEST_ACCESS_TOKEN']);
        foreach ($result['messages'] as $message) {
            foreach ($message['fileList'] as $file_info) {
                $file = $dl_client->request([
                    'key'      => $file_info['objectKey'],
                    'sellerId' => $_ENV['TEST_SELLER_ID'],
                ]);

                // Content-Typeが存在するか
                $content_type = $file['content-type'] ?? null;
                $this->assertFalse(empty($content_type));
                // ファイルデータが存在するか
                $body = $file['body'] ?? null;
                $this->assertFalse(empty($body));
            }
        }
    }

    /**
     * 既読APIテスト
     *
     * @return void
     */
    public function testExternalTalkRead(): void
    {
        // 問い合わせを既読に変更
        $read_client = new ExternalTalkReadClient($_ENV['TEST_ACCESS_TOKEN']);
        $result      = $read_client->request([
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
            'sellerId' => $_ENV['TEST_SELLER_ID'],
        ]);

        // 更新に成功したか
        $this->assertSame('ok', $result['status']);

        // 問い合わせ取得
        $detail_client = new ExternalTalkDetailClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $detail_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
        ]);

        // セラー既読になっているか
        $is_seller_unRead = 'true' === $result['topic']['isSellerUnRead'] ? true : false;
        $this->assertFalse($is_seller_unRead);
    }

    /**
     * ファイル投稿API・メッセージ投稿APIテスト
     *
     * @return void
     */
    public function testExternalTalkAdd(): void
    {
        $body      = $this->faker->sentence;
        $file_path = $_ENV['TEST_TOPIC_FILE'];
        $mime_type = mime_content_type($file_path);
        $file_ext  = pathinfo($file_path, PATHINFO_EXTENSION);
        $file_name = "{$this->faker->word}.{$file_ext}";

        // ファイル投稿 ※例外が発生しなければ成功
        $file_client = new ExternalTalkFileAddClient($_ENV['TEST_ACCESS_TOKEN']);
        $result      = $file_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
            'file'     => new CURLFile($file_path, $mime_type, $file_name),
        ]);
        $object_key  = $result['objectKey'];
        $file_list   = [
            'fileName' => $file_name,
            'filePath' => $object_key,
            'fileExt'  => $file_ext,
        ];

        // メッセージ投稿 ※例外が発生しなければ成功
        $add_client = new ExternalTalkAddClient($_ENV['TEST_ACCESS_TOKEN']);
        $result     = $add_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
            'body'     => $body,
            'fileList' => [$file_list],
        ]);

        // トピックIDは一致するか
        $this->assertSame($_ENV['TEST_TOPIC_ID'], $result['topicid']);
        // メッセージIDは存在するか
        $message_id = $result['messageid'] ?? null;
        $this->assertFalse(empty($message_id));

        // 問い合わせ取得
        $detail_client = new ExternalTalkDetailClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $detail_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
        ]);

        // メッセージIDで投稿したメッセージを取得
        $message = null;
        foreach ($result['messages'] as $value) {
            if ($value['messageId'] != $message_id) {
                continue;
            }
            $message = $value;
        }

        // メッセージは存在するか
        $this->assertFalse(empty($message));
        // メッセージ内容は一致するか
        $this->assertSame($body, $message['body']);

        // ファイルを取得
        $file = current($message['fileList']);

        // ファイル名は一致するか
        $this->assertSame($file_name, $file['fileName']);
        // オブジェクトキーは一致するか
        $this->assertSame($object_key, $file['objectKey']);
        // 拡張子は一致するか
        $this->assertSame($file_ext, $file['fileExt']);
    }

    /**
     * 質問非公開APIテスト
     *
     * @return void
     */
    public function testExternalTalkPrivate(): void
    {
        // 非公開に変更 ※例外が発生しなければ成功
        $private_client = new ExternalTalkPrivateClient($_ENV['TEST_ACCESS_TOKEN']);
        $private_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
        ]);

        // 問い合わせ取得
        $detail_client = new ExternalTalkDetailClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $detail_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
        ]);

        // 非公開になっているか
        $this->assertTrue($result['topic']['isPrivate']);
    }

    /**
     * 質問完了APIテスト
     *
     * @return void
     */
    public function testExternalTalkComplete(): void
    {
        $condition_id = 1;

        // 完了に変更 ※例外が発生しなければ成功
        $complete_client = new ExternalTalkCompleteClient($_ENV['TEST_ACCESS_TOKEN']);
        $complete_client->request([
            'sellerId'            => $_ENV['TEST_SELLER_ID'],
            'topicId'             => $_ENV['TEST_TOPIC_ID'],
            'completeConditionId' => $condition_id,
        ]);
 
        // 問い合わせ取得
        $detail_client = new ExternalTalkDetailClient($_ENV['TEST_ACCESS_TOKEN']);
        $result        = $detail_client->request([
            'sellerId' => $_ENV['TEST_SELLER_ID'],
            'topicId'  => $_ENV['TEST_TOPIC_ID'],
        ]);
 
        // 完了になっているか
        $this->assertTrue($result['topic']['isComplete']);
        // 完了条件は一致するか
        $this->assertSame($condition_id, (int)$result['topic']['completeConditionId']);
    }

}
