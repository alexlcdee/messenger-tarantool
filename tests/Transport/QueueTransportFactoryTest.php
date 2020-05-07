<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tests\Transport;

use AlexLcDee\Messenger\Tarantool\Tarantool\PhpClientFactory;
use AlexLcDee\Messenger\Tarantool\Transport\QueueTransport;
use AlexLcDee\Messenger\Tarantool\Transport\QueueTransportFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class QueueTransportFactoryTest extends TestCase
{
    public function test_can_create_messenger_transport()
    {
        $factory = new QueueTransportFactory(new PhpClientFactory(), new NullLogger());

        $transport = $factory->createTransport(
            'tarantool://tcp://127.0.0.1:3301?queue_name=test_queue',
            [],
            new PhpSerializer()
        );

        $this->assertInstanceOf(QueueTransport::class, $transport);
    }

    public function test_factory_supports_tarantool_dsn()
    {
        $factory = new QueueTransportFactory(new PhpClientFactory(), new NullLogger());

        $this->assertTrue($factory->supports('tarantool://tcp://127.0.0.1:3301?queue_name=test_queue', []));
    }
}
