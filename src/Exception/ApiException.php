<?php

namespace JSPSystem\YahooShoppingApiClient\Exception;

use Exception;

/**
 * Web API例外処理
 */
class ApiException extends Exception
{
    /**
     * エラー詳細
     *
     * @var string|null
     */
    public $error_detail = null;

    /**
     * インスタンス生成
     *
     * @param string $error エラー概要
     * @param string $error_detail エラー詳細
     * @param integer $code エラーコード
     * @param Exception|null $previous
     */
    public function __construct(
        $error,
        $error_detail = '',
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($error, $code, $previous);
        $this->error_detail = $error_detail;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return __CLASS__ . ": {$this->message}( {$this->error_detail} )";
    }

}
