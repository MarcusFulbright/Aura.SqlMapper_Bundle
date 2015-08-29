<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderLocator;
use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingAggregateFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\EmployeeFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\TaskAggregateFactory;

class AggregateGenerator
{
    protected $employee = [
        'root' => 'user_entity',
        'aggregates' => [
            'building_aggregate',
            'task_aggregate'
        ],
        'entities' => [
            'floor_entity',
            'user_entity'
        ],
        'relations' => [
            'user_to_floor',
            'task_aggregate_to_user',
            'user_to_building_aggregate'
        ]
    ];

    protected $building = [
        'root' => 'building_entity',
        'aggregates' => [],
        'entities' => [
            'building_entity',
            'building_type_entity'
        ],
        'relations' => [
            'building_to_type'
        ]
    ];

    protected $task = [
        'root' => 'task_entity',
        'aggregates' => [],
        'entities' => [
            'task_entity',
            'task_type_entity'
        ],
        'relations' => [
            'task_to_type'
        ]
    ];

    public function getBuilderLocator(array $aggregates)
    {
        $builders = [];
        foreach ($aggregates as $aggregate) {
            $method = 'get'.ucfirst($aggregate).'Builder';
            $key = $aggregate.'_aggregate';
            $builders[$key] = function() use ($method) {
                return $this->$method();
            };
        }
        return new AggregateBuilderLocator($builders);
    }

    public function getEmployeeBuilder()
    {
        $builder = new FakeAggregateBuilder(
            new EmployeeFactory(),
            new Filter()
        );
        $builder->setAggregates($this->employee['aggregates']);
        $builder->setEntities($this->employee['entities']);
        $builder->setRoot($this->employee['root']);
        $builder->setRelations($this->employee['relations']);
        return $builder;
    }

    public function getBuildingBuilder()
    {
        $builder = new FakeAggregateBuilder(
            new BuildingAggregateFactory(),
            new Filter()
        );
        $builder->setAggregates($this->building['aggregates']);
        $builder->setEntities($this->building['entities']);
        $builder->setRoot($this->building['root']);
        $builder->setRelations($this->building['relations']);
        return $builder;
    }

    public function getTaskBuilder()
    {
        $builder = new FakeAggregateBuilder(
            new TaskAggregateFactory(),
            new Filter()
        );
        $builder->setAggregates($this->task['aggregates']);
        $builder->setEntities($this->task['entities']);
        $builder->setRoot($this->task['root']);
        $builder->setRelations($this->task['relations']);
        return $builder;
    }
}