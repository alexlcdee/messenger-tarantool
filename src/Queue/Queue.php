<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Queue;


use Tarantool\Queue\Task;

/**
 * Interface Queue
 * @package AlexLcDee\Messenger\Tarantool\Queue
 *
 * @internal
 */
interface Queue
{
    public function put($data, array $options = []) : Task;

    public function take(float $timeout = null) : ?Task;

    public function ack(int $taskId) : Task;

    public function delete(int $taskId) : Task;
}