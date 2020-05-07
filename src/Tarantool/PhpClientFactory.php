<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tarantool;


use Tarantool\Client\Client;

final class PhpClientFactory implements ClientFactory
{
    public function fromDsn(string $dsn): Client
    {
        return Client::fromDsn($dsn);
    }
}