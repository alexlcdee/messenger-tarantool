<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tarantool;


interface ClientFactory
{
    /**
     * @param string $dsn
     * @return \Tarantool|\Tarantool\Client\Client
     */
    public function fromDsn(string $dsn);
}