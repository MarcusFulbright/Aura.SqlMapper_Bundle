<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Entity\EntityMapperLocator;
use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlMapper_Bundle\Row\GatewayLocator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingTypeFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\FloorFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\TaskFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\TaskTypeFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\UserFactory;

class EntityMapperGenerator
{
    protected $user = [
        'id'        => 'id',
        'name'      => 'name',
        'building'  => 'building',
        'floor'     => 'floor'
    ];

    protected $task = [
        'id'     => 'id',
        'userid' => 'userID',
        'name'   => 'name',
        'type'   => 'type'
    ];

    protected $building = [
        'id'   => 'id',
        'name' => 'name',
        'type' => 'type'
    ];

    protected $floor = [
        'id'   => 'id',
        'name' => 'name'
    ];

    protected $building_type = [
        'id'     => 'id',
        'code'   => 'code',
        'decode' => 'decode'
    ];

    public function getMapperLocator(array $entities, GatewayLocator $locator)
    {
        $mappers = [];
        foreach ($entities as $entity => $cache)
        {
            $method = 'get'.ucfirst($entity);
            $name = strtolower($entity).'_mapper';
            $mappers[$name] = function () use ($method, $locator) {
                return $this->$method($locator);
            };
        }
        return new EntityMapperLocator($mappers);
    }

    public function getUser(GatewayLocator $locator, $cache = null)
    {
        $mapper = new FakeEntityMapper(
            $locator->__get('user_gateway'),
            new UserFactory(),
            new Filter(),
            $cache
        );
        $mapper->setColsFields($this->user);
        return $mapper;
    }

    public function getTask(GatewayLocator $locator, $cache = null)
    {
        $mapper = new FakeEntityMapper(
            $locator->__get('task_gateway'),
            new TaskFactory(
                new TaskTypeFactory()
            ),
            new Filter(),
            $cache
        );
        $mapper->setColsFields($this->task);
        return $mapper;
    }

    public function getBuilding(GatewayLocator $locator, $cache = null)
    {
        $mapper = new FakeEntityMapper(
            $locator->__get('building_gateway'),
            new BuildingFactory(),
            new Filter(),
            $cache
        );
        $mapper->setColsFields($this->building);
        return $mapper;
    }

    public function getBuildingType(GatewayLocator $locator, $cache = null)
    {
        $mapper = new FakeEntityMapper(
            $locator->__get('building_type_gateway'),
            new BuildingTypeFactory(),
            new Filter(),
            $cache
        );
        $mapper->setColsFields($this->building_type);
        return $mapper;
    }

    public function getFloor(GatewayLocator $locator, $cache = null)
    {
        $mapper = new FakeEntityMapper(
            $locator->__get('floor_gateway'),
            new FloorFactory(),
            new Filter(),
            $cache
        );
        $mapper->setColsFields($this->floor);
        return $mapper;
    }
}