<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\Tests\Fixtures\Aggregates\TaskAggregate;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Task;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\TaskType;

class TaskAggregateFactory
{
    public function newObject(Task $task, TaskType $task_type)
    {
        return new TaskAggregate($task,$task_type);
    }

    public function newCollection(array $rows)
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->newObject($row['task'], $row['type']);
        }
        return $collection;
    }
}