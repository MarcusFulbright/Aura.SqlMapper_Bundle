<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderLocator;
use Aura\SqlMapper_Bundle\EntityMediation\EntityOperationFactory;
use Aura\SqlMapper_Bundle\EntityMediation\OperationManager;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceHolderFactory;
use Aura\SqlMapper_Bundle\Relations\RelationLocator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\AggregateGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingAggregateFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingTypeFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\EmployeeFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\FloorFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\TaskAggregateFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\TaskFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\TaskTypeFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\UserFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\RelationGenerator;

class OperationManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  OperationManager */
    protected $manager;

    /** @var AggregateBuilderLocator */
    protected $builder_locator;

    /** @var RelationLocator */
    protected $relation_locator;

    /** @var PlaceHolderFactory */
    protected $placeholder_factory;

    /** @var EntityOperationFactory */
    protected $operation_factory;

    protected function getEmployeeOrder ()
    {
        return [
            0 => (object)[
                'relation_name' => 'user_to_floor',
                'relation'      => $this->relation_locator->__get('user_to_floor'),
                'entities'      => [
                    'inverse' => 'floor_entity'
                ]
            ],
            1 => (object)[
                'relation_name' => 'user_to_building_aggregate',
                'relation'      => $this->relation_locator->__get('user_to_building_aggregate'),
                'entities'      => [
                    'inverse' => 'building_aggregate'
                ]
            ],
            2 => (object)[
                'relation_name' => 'task_aggregate_to_user',
                'relation'      => $this->relation_locator->__get('task_aggregate_to_user'),
                'entities'      => [
                    'inverse' => 'user_entity',
                    'owning'  => 'task_aggregate'
                ],
            ]
        ];
    }

    protected function getBuildingAggregateOrder()
    {
        return [
            0 => (object)[
                'relation_name' => 'building_to_type',
                'relation'      => $this->relation_locator->__get('building_to_type'),
                'entities'      => [
                    'inverse' => 'building_type_entity',
                    'owning'  => 'building_entity'
                ]
            ]
        ];
    }

    public function setUp ()
    {
        $aggregate_generator = new AggregateGenerator();
        $this->builder_locator = $aggregate_generator->getBuilderLocator(['employee', 'building']);
        $relation_generator = new RelationGenerator();
        $this->relation_locator = $relation_generator->getRelationLocator(
            [
                'user_to_floor',
                'task_aggregate_to_user',
                'user_to_building_aggregate',
                'building_to_type'
            ]);
        $this->placeholder_factory = new PlaceHolderFactory();
        $this->operation_factory = new EntityOperationFactory();
        $this->manager = new OperationManager(
            $this->placeholder_factory,
            $this->relation_locator,
            $this->operation_factory
        );
    }

    public function testGetOrderForEmployee()
    {
        $expected = $this->getEmployeeOrder();
        $actual = $this->manager->getOrder($this->builder_locator->__get('employee'));
        $this->assertEquals($expected, $actual);
    }

    public function testGetOrderForBuildingAggregate()
    {
        $expected = $this->getBuildingAggregateOrder();
        $actual = $this->manager->getOrder ($this->builder_locator->__get ('building'));
        $this->assertEquals ($expected, $actual);
    }

    public function testGetOperationListBuildingAggregate()
    {
        $building_type_factory = new BuildingTypeFactory();
        $type_entity = $building_type_factory->newObject(
            [
                'id'     => null,
                'code'   => 'P',
                'decode' => 'Profit'
            ]);
        $building_factory = new BuildingFactory();
        $building_entity = $building_factory->newObject(
            [
                'id'   => null,
                'name' => 'NewBuildingName',
                'type' => $type_entity->getCode()
            ]);
        $expected = [
            0 => $this->operation_factory->newOperation(
                'building_type_entity',
                $type_entity,
                []
            ),
            1 => $this->operation_factory->newOperation(
                'building_entity',
                $building_entity,
                ['type' => $this->placeholder_factory->newObjectPlaceHolder($type_entity, 'code')]
            )
        ];
        $actual = $this->manager->getOperationList(
            $this->getBuildingAggregateOrder(),
            [
                'building_to_type' => [
                    0 => [
                        'building_type_entity' => $type_entity,
                        'building_entity'      => $building_entity
                    ]
                ]
            ]
        );
        $this->assertEquals ($expected, $actual);
    }

    public function testGetOperationListEmployee()
    {
        $building_factory = new BuildingFactory();
        $building = $building_factory->newObject(
            [
                'id'   => 1,
                'name' => 'building_entity',
                'type' => 'P'
            ]);

        $building_type_factory = new BuildingTypeFactory();
        $building_type = $building_type_factory->newObject(
            [
                'id'     => 1,
                'code'   => 'P',
                'decode' => 'Profit'
            ]);
        $building_aggregate_factory = new BuildingAggregateFactory();
        $building_aggregate = $building_aggregate_factory->newObject($building, $building_type);

        $task_factory = new TaskFactory();
        $task = $task_factory->newObject(
            [
                'id'     => 1,
                'name'   => 'task_1',
                'type'   => 'B',
                'userid' => 1
            ]);
        $task_type_factory = new TaskTypeFactory();
        $task_type = $task_type_factory->newObject(
            [
                'id'     => 1,
                'code'   => 'B',
                'decode' => 'Budget'
            ]);
        $task_aggregate_factory = new TaskAggregateFactory();
        $task_aggregate = $task_aggregate_factory->newObject($task, $task_type);

        $floor_factory = new FloorFactory();
        $floor = $floor_factory->newObject(
            [
                'id'   => 1,
                'name' => 'floor'
            ]);

        $user_factory = new UserFactory();
        $user = $user_factory->newObject(
            [
                'id'       => 1,
                'name'     => 'user',
                'floor'    => 1,
                'building' => 1
            ]);
        $actual = $this->manager->getOperationList(
            $this->getEmployeeOrder(),
            [
                'user_to_floor' => [
                    0 => [
                        'user_entity'  => $user,
                        'floor_entity' => $floor
                    ]
                ],
                'user_to_building_aggregate' => [
                    0 => [
                        'building_aggregate' => $building_aggregate,
                        'user_entity'        => $user
                    ]
                ],
                'task_aggregate_to_user' => [
                    0 => [
                        'user_entity'    => $user,
                        'task_aggregate' => [
                            $task_aggregate
                        ]
                    ]
                ]
            ]
        );
        $expected = [
            0 => $this->operation_factory->newOperation (
                'floor_entity', $floor, []
            ),
            1 => $this->operation_factory->newOperation (
                'building_aggregate', $building_aggregate, []
            ),
            2 => $this->operation_factory->newOperation (
                'user_entity', $user, []
            ),
            3 => $this->operation_factory->newOperation (
                'task_aggregate',
                [$task_aggregate],
                ['userid' => $this->placeholder_factory->newObjectPlaceHolder ('id', $user)]
            )
        ];
        $this->assertEquals($expected, $actual);
    }
}