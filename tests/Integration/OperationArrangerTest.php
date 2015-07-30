<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderLocator;
use Aura\SqlMapper_Bundle\EntityMediation\EntityOperationFactory;
use Aura\SqlMapper_Bundle\EntityMediation\OperationManager;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceHolderFactory;
use Aura\SqlMapper_Bundle\Relations\RelationLocator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\AggregateGenerator;
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
        $this->manager = new OperationManager(
            new EntityOperationFactory(),
            new PlaceHolderFactory(),
            $this->relation_locator
        );

        $fixtures = new SqliteFixture($gateway_gen->getConnection()->getWrite());
        $fixtures->exec();
    }

    public function testGetOrderForEmployee()
    {
        $expected = [
            'floor_entity',
            'building_aggregate',
            'user_entity',
            'task_aggregate'
        ];
        $actual = $this->manager->getOrder($this->builder_locator->__get('employee'));
        $this->assertEquals($expected, $actual);
    }

    public function testGetOrderForTask()
    {
        $expected = [
            'task_type_entity',
            'task_entity'
        ];
        $actual = $this->manager->getOrder($this->builder_locator->__get('task'));
        $this->assertEquals($expected, $actual);
    }
}
