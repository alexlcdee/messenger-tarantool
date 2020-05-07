<?php

declare(strict_types=1);

namespace AlexLcDee\Messenger\Tarantool\Tests\Transport;


use AlexLcDee\Messenger\Tarantool\Queue\Queue;
use Tarantool\Queue\States;
use Tarantool\Queue\Task;

class QueueStub implements Queue
{
    /**
     * @var Task[]
     */
    private array $tasks = [];

    private int $id = 1;

    public function getTask(int $taskId): ?Task
    {
        return $this->tasks[$taskId] ?? null;
    }

    public function setTask(Task $task)
    {
        $this->tasks[$task->getId()] = $task;
    }

    public function put($data, array $options = []): Task
    {
        $task = Task::createFromTuple([
            $this->id++,
            States::READY,
            $data,
        ]);
        $this->setTask($task);

        return $task;
    }

    public function take(float $timeout = null): ?Task
    {
        $readyTasks = array_filter($this->tasks, fn(Task $task) => $task->isReady());
        if (count($readyTasks) === 0) {
            return null;
        }
        /** @var Task $task */
        $task = self::morphAttributes($readyTasks[array_key_first($readyTasks)], [
            'state' => States::TAKEN,
        ]);
        $this->setTask($task);

        return $task;
    }

    public function ack(int $taskId): Task
    {
        /** @var Task $task */
        $task = self::morphAttributes($this->tasks[$taskId], [
            'state' => States::DONE,
        ]);
        $this->setTask($task);

        return $this->tasks[$taskId];
    }

    public function delete(int $taskId): Task
    {
        $task = $this->tasks[$taskId];
        unset($this->tasks[$taskId]);

        return $task;
    }

    /** @var \ReflectionClass[] */
    private static array $reflections = [];

    private static function morphAttribute(object $object, string $attributeName, $value)
    {
        if (!isset(self::$reflections[get_class($object)])) {
            self::$reflections[get_class($object)] = new \ReflectionClass($object);
        }
        $reflection = self::$reflections[get_class($object)];
        try {
            $property = $reflection->getProperty($attributeName);
            $property->setAccessible(true);
            $property->setValue($object, $value);
        } catch (\ReflectionException $exception) {
        }

        return $object;
    }

    private static function morphAttributes(object $object, array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $object = self::morphAttribute($object, $name, $value);
        }

        return $object;
    }
}