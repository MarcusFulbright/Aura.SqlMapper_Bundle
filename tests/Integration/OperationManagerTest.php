<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderLocator;
use Aura\SqlMapper_Bundle\EntityMediation\EntityOperationFactory;
use Aura\SqlMapper_Bundle\EntityMediation\OperationManager;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceHolderFactory;
use Aura\SqlMapper_Bundle\Relations\RelationLocator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\AggregateGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Factories\BuildingTypeFactory;
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

    public function setUp()
    {
        $aggregate_generator = new AggregateGenerator();
        $this->builder_locator = $aggregate_generator->getBuilderLocator(['employee', 'building']);
        $relation_generator = new RelationGenerator();
        $this->relation_locator = $relation_generator->getRelationLocator([
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
        $expected = [
            0 => (object)[
                'relation_name' => 'user_to_floor',
                'relation' => $this->relation_locator->__get('user_to_floor'),
                'entities' => [
                    'inverse' => 'floor_entity'
                ]
            ],
            1 => (object)[
                'relation_name' => 'user_to_building_aggregate',
                'relation' => $this->relation_locator->__get('user_to_building_aggregate'),
                'entities' => [
                    'inverse' => 'building_aggregate'
                ]
            ],
            2 => (object)[
                'relation_name' => 'task_aggregate_to_user',
                'relation' => $this->relation_locator->__get('task_aggregate_to_user'),
                'entities' => [
                    'inverse' => 'user_entity',
                    'owning' => 'task_aggregate'
                ],
            ]
        ];
        $actual = $this->manager->getOrder($this->builder_locator->__get('employee'));
        $this->assertEquals($expected, $actual);
    }

    public function testGetOrderForBuildingAggregate()
    {
        $expected = [
            0 => (object)[
                'relation_name' => 'building_to_type',
                'relation' => $this->relation_locator->__get('building_to_type'),
                'entities' => [
                    'inverse' => 'building_type_entity',
                    'owning' => 'building_entity'
                ]
            ]
        ];
        $actual = $this->manager->getOrder($this->builder_locator->__get('building'));
        $this->assertEquals($expected, $actual);
    }

    public function testGetOperationListBuildingAggregate()
    {
        $building_type_factory = new BuildingTypeFactory();
        $type_entity = $building_type_factory->newObject([
            'id' => null,
            'code' => 'P',
            'decode' => 'Profit'
        ]);
        $building_factory = new BuildingFactory();
        $building_entity = $building_factory->newObject([
            'id' => null,
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
            [
                0 => (object)[
                    'relation_name' => 'building_to_type',
                    'relation' => $this->relation_locator->__get('building_to_type'),
                    'entities' => [
                        'inverse' => 'building_type_entity',
                        'owning' => 'building_entity'
                    ]
                ]
            ],
            [
                'building_to_type' => [
                    0 => [
                        'building_type_entity' => $type_entity,
                        'building_entity' => $building_entity
                    ]
                ]
            ]
        );
        $this->assertEquals($expected, $actual);
    }

    public function testGetOperationListEmployee()
    {

    }
}
