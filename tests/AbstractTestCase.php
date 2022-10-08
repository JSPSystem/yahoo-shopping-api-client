<?php

namespace JSPSystem\YahooShoppingApiClient\Tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected $faker;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        Dotenv::createImmutable(__DIR__ . '/../')->load();
        $this->faker = \Faker\Factory::create();

        parent::__construct($name, $data, $dataName);
    }

}
