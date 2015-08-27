<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Task;

class TaskFactory implements ObjectFactoryInterface
{
    public function newObject(array $row)
    {
        $task = new Task();
        $task->setId($row['id']);
        $task->setName($row['name']);
        $task->setUserID($row['userid']);
        $task->setType($row['type']);
        return $task;
    }

    public function newCollection(array $rows)
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->newObject($row);
        }
        return $collection;
    }
}