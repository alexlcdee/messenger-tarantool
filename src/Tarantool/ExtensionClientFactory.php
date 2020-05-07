<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tarantool;


use Tarantool\Client\Client;

/**
 * Class ExtensionClientFactory
 * @package AlexLcDee\Messenger\Tarantool\Tarantool
 *
 * @deprecated Outdated Client. Use it only for your own risk!
 * @codeCoverageIgnore Outdated client. I don't gonna test this!
 */
final class ExtensionClientFactory implements ClientFactory
{
    public function fromDsn(string $dsn): \Tarantool
    {
        $params = self::parse($dsn);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return new \Tarantool(
            $params['host'],
            $params['port'],
            $params['username'] ?? 'guest',
            $params['password']
        );
    }

    private static function parse(string $dsn): array
    {
        if (false === $parsed = \parse_url($dsn)) {
            self::throwParseError($dsn);
        }
        if (!isset($parsed['scheme'], $parsed['host']) || 'tcp' !== $parsed['scheme']) {
            self::throwParseError($dsn);
        }
        if (isset($parsed['path']) && '/' !== $parsed['path']) {
            self::throwParseError($dsn);
        }

        $result = [];
        $result['host'] = $parsed['host'];
        $result['port'] = $parsed['port'] ?? 3301;

        if (isset($parsed['user'])) {
            $result['username'] = \rawurldecode($parsed['user']);
            $result['password'] = isset($parsed['pass']) ? \rawurldecode($parsed['pass']) : '';
        }

        return $result;
    }

    private static function throwParseError(string $dsn) : void
    {
        throw new \InvalidArgumentException(\sprintf('Unable to parse DSN "%s"', $dsn));
    }
}