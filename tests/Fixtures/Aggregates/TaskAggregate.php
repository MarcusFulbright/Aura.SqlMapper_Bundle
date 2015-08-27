<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Aggregates;

use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Task;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\TaskType;

class TaskAggregate
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $user_id;

    /** @var string */
    protected $name;

    /** @var TaskType */
    protected $type;

    public function __construct(Task $task, TaskType $type)
    {
        $this->id      = $task->getId();
        $this->user_id = $task->getUserID();
        $this->name    = $task->getName();
        $this->type    = $type;
    }

    /** @return int */
    public function getId ()
    {
        return $this->id;
    }

    /** @param int $id */
    public function setId ($id)
    {
        $this->id = $id;
    }

    /** @return int */
    public function getUserId ()
    {
        return $this->user_id;
    }

    /** @param int $user_id */
    public function setUserId ($user_id)
    {
        $this->user_id = $user_id;
    }

    /** @return string */
    public function getName ()
    {
        return $this->name;
    }

    /** @param string $name */
    public function setName ($name)
    {
        $this->name = $name;
    }

    /** @return TaskType */
    public function getType ()
    {
        return $this->type;
    }

    /** @param TaskType $type */
    public function setType ($type)
    {
        $this->type = $type;
    }
}