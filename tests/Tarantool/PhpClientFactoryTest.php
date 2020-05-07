<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tests\Tarantool;

use AlexLcDee\Messenger\Tarantool\Tarantool\PhpClientFactory;
use PHPUnit\Framework\TestCase;
use Tarantool\Client\Client;

class PhpClientFactoryTest extends TestCase
{
    public function test_can_create_php_client_from_dsn()
    {
        $clientFactory = new PhpClientFactory();

        $client = $clientFactory->fromDsn('tcp://127.0.0.1:3301');

        $this->assertInstanceOf(Client::class, $client);
    }
}
