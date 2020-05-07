<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Transport;


use AlexLcDee\Messenger\Tarantool\Queue\Queue;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Tarantool\Client\Exception\CommunicationFailed;
use Tarantool\Queue\Options;

final class QueueTransport implements TransportInterface
{
    /**
     * @var Queue
     */
    private Queue $queue;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(Queue $queue, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->queue = $queue;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function get(): iterable
    {
        for ($i = 5; $i > 0; $i--) {
            try {
                $task = $this->queue->take(0.02);
                if ($task !== null) {
                    yield $this->serializer->decode($task->getData())->with(new TransportMessageIdStamp($task->getId()));
                }
            } catch (CommunicationFailed $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function ack(Envelope $envelope): void
    {
        $this->queue->ack($envelope->last(TransportMessageIdStamp::class)->getId());
    }

    /**
     * @inheritDoc
     */
    public function reject(Envelope $envelope): void
    {
        $this->queue->delete($envelope->last(TransportMessageIdStamp::class)->getId());
    }

    /**
     * @inheritDoc
     */
    public function send(Envelope $envelope): Envelope
    {
        $content = $this->serializer->encode($envelope);
        $options = [];
        /** @var DelayStamp|null $delay */
        $delay = $envelope->last(DelayStamp::class);
        if ($delay !== null) {
            $options[Options::DELAY] = $delay->getDelay();
        }
        $task = $this->queue->put($content, $options);

        return $envelope->with(new TransportMessageIdStamp($task->getId()));
    }
}