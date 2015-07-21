<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Task;

class TaskFactory implements ObjectFactoryInterface
{
    /** @var TaskTypeFactory  */
    protected $task_type_factory;

    public function __construct(TaskTypeFactory $task_type_factory)
    {
        $this->task_type_factory = $task_type_factory;
    }

    public function newObject(array $row)
    {
        $task = new Task();
        $task->setId($row['id']);
        $task->setName($row['name']);
        $task->setUserID($row['userId']);
        $task->setType($this->newTaskType($row['type']));
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

    protected function newTaskType(array $row)
    {
        return $this->task_type_factory->newObject($row);
    }
}