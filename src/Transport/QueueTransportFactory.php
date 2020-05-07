<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Transport;


use AlexLcDee\Messenger\Tarantool\Queue\TarantoolQueueAdapter;
use AlexLcDee\Messenger\Tarantool\Tarantool\ClientFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Tarantool\Queue\Queue;

final class QueueTransportFactory implements TransportFactoryInterface
{
    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(ClientFactory $clientFactory, LoggerInterface $logger)
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $dsn = str_replace('tarantool://', '', $dsn);
        $parsedUrl = parse_url($dsn);
        parse_str($parsedUrl['query'], $tarantoolOptions);
        $name = $tarantoolOptions['queue_name'];
        $client = $this->clientFactory->fromDsn($dsn);

        return new QueueTransport(new TarantoolQueueAdapter(new Queue($client, $name)), $serializer, $this->logger);
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'tarantool://');
    }
}