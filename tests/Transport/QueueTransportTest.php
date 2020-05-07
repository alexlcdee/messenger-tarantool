<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tests\Transport;

use AlexLcDee\Messenger\Tarantool\Transport\QueueTransport;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Tarantool\Queue\States;
use Tarantool\Queue\Task;

class QueueTransportTest extends TestCase
{
    private QueueTransport $queueTransport;

    private PhpSerializer $serializer;

    private QueueStub $queueStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = new PhpSerializer();

        $this->queueStub = new QueueStub();

        $this->queueTransport = new QueueTransport(
            $this->queueStub,
            $this->serializer,
            new NullLogger()
        );
    }

    public function test_can_get_tasks()
    {
        $messageId = \mt_rand();
        $this->queueStub->setTask(Task::createFromTuple([
            1,
            States::READY,
            $this->serializer->encode(new Envelope(new Message($messageId))),
        ]));
        $foundTasks = 0;
        foreach ($this->queueTransport->get() as $envelope) {
            $foundTasks++;
            $this->assertInstanceOf(Envelope::class, $envelope);
            $this->assertInstanceOf(Message::class, $envelope->getMessage());
            $this->assertEquals($messageId, $envelope->getMessage()->id);
        }

        $this->assertEquals(1, $foundTasks);
    }

    public function test_can_send_tasks()
    {
        $messageId = \mt_rand();
        $message = new Envelope(new Message($messageId));

        $messageSent = $this->queueTransport->send($message);

        $task = $this->queueStub->getTask($messageSent->last(TransportMessageIdStamp::class)->getId());
        $taskMessage = $this->serializer->decode($task->getData());

        $this->assertInstanceOf(Envelope::class, $taskMessage);
        $this->assertInstanceOf(Message::class, $taskMessage->getMessage());
        $this->assertEquals($messageId, $taskMessage->getMessage()->id);
    }

    public function test_can_ack_task()
    {
        $messageId = \mt_rand();
        $envelope = (new Envelope(new Message($messageId)))
            ->with(new TransportMessageIdStamp(1));
        $this->queueStub->setTask(Task::createFromTuple([
            1,
            States::READY,
            $this->serializer->encode($envelope),
        ]));

        $this->queueTransport->ack($envelope);

        $this->assertEquals(States::DONE, $this->queueStub->getTask(1)->getState());
    }

    public function test_can_reject_task()
    {
        $messageId = \mt_rand();
        $envelope = (new Envelope(new Message($messageId)))
            ->with(new TransportMessageIdStamp(1));
        $this->queueStub->setTask(Task::createFromTuple([
            1,
            States::READY,
            $this->serializer->encode($envelope),
        ]));

        $this->queueTransport->reject($envelope);

        $this->assertNull($this->queueStub->getTask(1));
    }
}

class Message
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
