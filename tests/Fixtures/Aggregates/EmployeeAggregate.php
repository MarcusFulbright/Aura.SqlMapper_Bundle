<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Aggregates;

use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Floor;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\User;

class EmployeeAggregate
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var BuildingAggregate */
    protected $building;

    /** @var Floor */
    protected $floor;

    /** @var array */
    protected $tasks;

    /**
     * @param User              $user
     * @param Floor             $floor
     * @param BuildingAggregate $building
     * @param array             $tasks
     */
    public function __construct(User $user, Floor $floor, BuildingAggregate $building, array $tasks)
    {
        $this->id       = $user->getId();
        $this->name     = $user->getName();
        $this->building = $building;
        $this->floor    = $floor;
        $this->tasks    = $tasks;
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

    /** @return BuildingAggregate */
    public function getBuilding ()
    {
        return $this->building;
    }

    /** @param BuildingAggregate $building */
    public function setBuilding ($building)
    {
        $this->building = $building;
    }

    /** @return Floor */
    public function getFloor ()
    {
        return $this->floor;
    }

    /** @param Floor $floor */
    public function setFloor ($floor)
    {
        $this->floor = $floor;
    }

    /** @return array */
    public function getTasks ()
    {
        return $this->tasks;
    }

    /** @param array $tasks */
    public function setTasks ($tasks)
    {
        $this->tasks = $tasks;
    }
}