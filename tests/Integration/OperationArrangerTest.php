<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderLocator;
use Aura\SqlMapper_Bundle\EntityMediation\EntityOperation;
use Aura\SqlMapper_Bundle\EntityMediation\EntityOperationFactory;
use Aura\SqlMapper_Bundle\EntityMediation\OperationManager;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceHolderFactory;
use Aura\SqlMapper_Bundle\Relations\RelationLocator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\AggregateGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingTypeFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\GatewayGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\RelationGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\SqliteFixture;

class OperationArrangerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  OperationManager */
    protected $manager;

    /** @var AggregateBuilderLocator */
    protected $builder_locator;

    /** @var RelationLocator */
    protected $relation_locator;

    /** @var EntityOperationFactory */
    protected $operation_factory;

    /** @var BuildingFactory */
    protected $building_factory;

    /** @var BuildingTypeFactory */
    protected $building_type_factory;

    /** @var PlaceHolderFactory */
    protected $place_holder_factory;

    public function setUp()
    {
        $aggregate_gen = new AggregateGenerator();
        $gateway_gen   = new GatewayGenerator();
        $relation_gen  = new RelationGenerator();
        $aggregates = ['employee', 'building', 'task'];
        $relations = [
            'building_to_type',
            'task_to_type',
            'user_to_floor',
            'task_aggregate_to_user',
            'user_to_building_aggregate'
        ];

        $this->builder_locator = $aggregate_gen->getBuilderLocator($aggregates);
        $this->relation_locator = $relation_gen->getRelationLocator($relations);
        $this->operation_factory = new EntityOperationFactory();
        $this->manager = new OperationManager(
            $this->operation_factory,
            new PlaceHolderFactory(),
            $this->relation_locator
        );

        $fixtures = new SqliteFixture($gateway_gen->getConnection()->getWrite());
        $fixtures->exec();

        $this->building_factory = new BuildingFactory();
        $this->building_type_factory = new BuildingTypeFactory();
        $this->place_holder_factory = new PlaceHolderFactory();
    }

    public function testGetOrderForEmployee()
    {
        $expected = [
            0 => [
                'user_to_floor' => [
                    'entities' => [
                        'inverse' => 'floor_entity'
                    ],
                    'relation' => $this->relation_locator->__get('user_to_floor')
                ],
            ],
            1 => [
                'user_to_building_aggregate' => [
                    'entities' => [
                        'inverse' => 'building_aggregate'
                    ],
                    'relation' => $this->relation_locator->__get('user_to_building_aggregate')
                ],
            ],
            2 => [
                'task_aggregate_to_user' => [
                'entities' => [
                    'inverse' => 'user_entity',
                    'owning' => 'task_aggregate'
                ],
                'relation' => $this->relation_locator->__get('task_aggregate_to_user')
            ]
        ];
        $actual = $this->manager->getOrder($this->builder_locator->__get('employee'));
        $this->assertEquals($expected, $actual);
    }
/*
    public function testGetOrderForTask()
    {
        $expected = [
            'task_to_type' => [
                'entities' => [
                    'inverse' => 'task_type_entity',
                    'owning' => 'task_entity'
                ],
                'relation' => $this->relation_locator->__get('task_to_type')
            ]
        ];
        $actual = $this->manager->getOrder($this->builder_locator->__get('task'));
        $this->assertEquals($expected, $actual);
    }

    public function testGorOrderForBuilding()
    {
        $expected = [
            'building_to_type' => [
                'entities' => [
                    'inverse' => 'building_type_entity',
                    'owning' => 'building_entity'
                ],
                'relation' => $this->relation_locator->__get('building_to_type')
            ]
        ];
        $actual = $this->manager->getOrder($this->builder_locator->__get('building'));
        $this->assertEquals($expected, $actual);
    }

    public function testGetOperationsForBuilding()
    {
        $building = $this->building_factory->newObject(['id' => 1, 'name' => 'building 1', 'type' => 'P']);
        $type = $this->building_type_factory->newObject(['id' => 2, 'code' => 'P', 'decode' => 'Profit']);
        $place_holder = $this->place_holder_factory->getObjectPlaceHolder($type, 'code');
        $pieces = [
            [
                'building_entity'      => [$building],
                'building_type_entity' => [$type]
            ]
        ];
        $order = [
            'building_type_entity' => null,
            'building_entity'      => $this->relation_locator->__get('building_to_type')
        ];
        $expected = [
            [
                'building_to_type' => [
                    'building_type_entity' => [
                        new EntityOperation('building_type_entity', $type),
                    ],
                    'building_entity' => [
                        new EntityOperation('building_entity', $building, ['type' => $place_holder])
                    ]
                ]
            ]
        ];
        $actual = $this->manager->getOperationList($order, $pieces);
        $this->assertEquals($expected, $actual);
    }
*/
}
