<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tarantool;


class SelectorClientFactory implements ClientFactory
{
    public function fromDsn(string $dsn)
    {
        if(class_exists(\Tarantool\Client\Client::class)) {
            return (new PhpClientFactory())->fromDsn($dsn);
        } elseif(class_exists(\Tarantool::class)) {
            return (new ExtensionClientFactory())->fromDsn($dsn);
        } else {
            $message = <<<TXT
No suitable implementations of Tarantool Client found!

You must use php tarantool/client 
```composer require tarantool/client```
or tarantool pecl extension
```
pecl channel-discover tarantool.github.io/tarantool-php/pecl
pecl install Tarantool-PHP/Tarantool-beta
```
TXT;

            throw new \LogicException($message);
        }
    }
}