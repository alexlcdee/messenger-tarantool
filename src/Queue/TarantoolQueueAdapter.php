<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Queue;


use Tarantool\Queue\Task;

/**
 * Class TarantoolQueueAdapter
 * @package AlexLcDee\Messenger\Tarantool\Queue
 *
 * @internal
 */
final class TarantoolQueueAdapter implements Queue
{
    private \Tarantool\Queue\Queue $queue;

    public function __construct(\Tarantool\Queue\Queue $queue)
    {
        $this->queue = $queue;
    }

    public function put($data, array $options = []): Task
    {
        return $this->queue->put($data, $options);
    }

    public function take(float $timeout = null): ?Task
    {
        return $this->queue->take($timeout);
    }

    public function ack(int $taskId): Task
    {
        return $this->queue->ack($taskId);
    }

    public function delete(int $taskId): Task
    {
        return $this->queue->delete($taskId);
    }
}