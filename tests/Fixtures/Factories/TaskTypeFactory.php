<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\TaskType;

class TaskTypeFactory implements ObjectFactoryInterface
{
    public function newObject(array $row)
    {
        $task_type = new TaskType();
        $task_type->setDecode($row['decode']);
        $task_type->setCode($row['code']);
        $task_type->setId($row['id']);
        return $task_type;
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