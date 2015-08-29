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

    protected function getEmployeeOrder()
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
                'relation_name' => 'building_to_type',
                'relation'      => $this->relation_locator->__get('building_to_type'),
                'entities'      => [
                    'inverse' => 'building_type_entity',
                    'owning'  => 'building_entity'
                ]
            ],
            2 => (object)[
                'relation_name' => 'task_aggregate_to_user',
                'relation'      => $this->relation_locator->__get('task_aggregate_to_user'),
                'entities'      => [
                    'inverse' => 'user_entity',
                ],
            ],
            3 => (object)[
                'relation_name' => 'task_to_type',
                'relation'      => $this->relation_locator->__get('task_to_type'),
                'entities'      => [
                    'inverse' => 'task_type_entity',
                    'owning'  => 'task_entity'
                ]
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

    public function setUp()
    {
        $aggregate_generator = new AggregateGenerator();
        $this->builder_locator = $aggregate_generator->getBuilderLocator(['employee', 'building', 'task']);
        $relation_generator = new RelationGenerator();
        $this->relation_locator = $relation_generator->getRelationLocator(
            [
                'user_to_floor',
                'task_aggregate_to_user',
                'user_to_building_aggregate',
                'building_to_type',
                'task_to_type'
            ]);
        $this->placeholder_factory = new PlaceHolderFactory();
        $this->operation_factory = new EntityOperationFactory();
        $this->manager = new OperationManager(
            $this->placeholder_factory,
            $this->relation_locator,
            $this->operation_factory,
            $this->builder_locator
        );
    }
/*
    public function testGetOrderForEmployee()
    {
        $expected = $this->getEmployeeOrder();
        $actual = $this->manager->getOrder($this->builder_locator->__get('employee_aggregate'));
        $this->assertEquals($expected, $actual);
    }

    public function testGetOrderForBuildingAggregate()
    {
        $expected = $this->getBuildingAggregateOrder();
        $actual = $this->manager->getOrder ($this->builder_locator->__get ('building_aggregate'));
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
*/
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
                'building_to_type' => [
                    0 => [
                        'building_entity' => $building,
                        'building_type_entity'=> $building_type
                    ]
                ],
                'task_aggregate_to_user' => [
                    0 => [
                        'user_entity' => $user
                    ]
                ],
                'task_to_type' => [
                    0 => [
                        'task_entity' => [$task],
                        'task_type_entity'   => [$task_type]
                    ]
                ]
            ]
        );
        $expected = [
            0 => $this->operation_factory->newOperation(
                'floor_entity', $floor, []
            ),
            1 => $this->operation_factory->newOperation(
                'building_type_entity', $building_type, []
            ),
            2 => $this->operation_factory->newOperation(
                'building_entity',
                $building,
                ['type' => $this->placeholder_factory->newObjectPlaceHolder('code', $building_type)]
            ),
            3 => $this->operation_factory->newOperation(
                'user_entity',
                $user,
                [
                    'floor'    => $this->placeholder_factory->newObjectPlaceHolder('id', $floor),
                    'building' => $this->placeholder_factory->newObjectPlaceHolder('id', $building)
                ]
            ),
            4 => $this->operation_factory->newOperation(
                'task_type_entity', [$task_type],[]
            ),
            5 => $this->operation_factory->newOperation(
                'task_entity',
                [$task],
                [
                    0 => [
                        'userid' => $this->placeholder_factory->newObjectPlaceHolder('id', $user),
                        'type'   => $this->placeholder_factory->newObjectPlaceHolder('code', $task_type)
                    ]
                ]
            )
        ];
        $this->assertEquals($expected, $actual);
    }
}