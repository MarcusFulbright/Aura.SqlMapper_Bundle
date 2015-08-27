<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Aggregates;

use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Building;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\BuildingType;

class BuildingAggregate
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var BuildingType */
    protected $type;

    public function __construct(Building $building, BuildingType $type)
    {
        $this->id = $building->getId();
        $this->name = $building->getName();
        $this->type = $type;
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

    /** @return BuildingType */
    public function getType ()
    {
        return $this->type;
    }

    /** @param BuildingType $type */
    public function setType (BuildingType $type)
    {
        $this->type = $type;
    }
}