<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tarantool;


use Tarantool\Client\Client;

interface ClientFactory
{
    /**
     * @param string $dsn
     * @return \Tarantool|Client
     */
    public function fromDsn(string $dsn);
}