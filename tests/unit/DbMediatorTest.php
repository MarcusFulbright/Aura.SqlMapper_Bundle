<?php
namespace Aura\SqlMapper_Bundle;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlQuery\QueryFactory;

/**
 * Test DbMediatorTest
 * @package Aura\SqlMapper_Bundle
 */
class DbMediatorTest extends \PHPUnit_Framework_TestCase 
{
    /** @var DbMediator */
    protected $mediator;

    /** @var  ConnectionLocator */
    protected $connection_locator;

    protected $query;

    /** @var Filter */
    protected $filter;

    /** @var Profiler */
    protected $profiler;

    /** @var MapperLocator */
    protected $mapper_locator;

    /** @var  AggregateMapperInterface */
    protected $aggregate_mapper;

    protected $data = [
        'aura_test_table' => [
            'primary' => 'id',
            'map' => [
                'id' => 'id',
                'name' => 'name',
                'building' => 'building',
                'floor' => 'floor'
            ]
        ],
        'aura_test_building' => [
            'primary' => 'id',
            'map' => [
                'id' => 'id',
                'name' => 'name',
                'type' => 'type'
            ]
        ],
        'aura_test_building_typeref' => [
            'primary' => 'id',
            'map' => [
                'id' => 'id',
                'code' => 'code',
                'decode' => 'decode'
            ]
        ],
        'aura_test_floor' => [
            'primary' => 'id',
            'map' => [
                'id' => 'id',
                'name' => 'name'
            ],
        ],
        'aura_test_task' => [
            'primary' => 'id',
            'map' => [
                'id' => 'id',
                'userId' => 'userid',
                'name' => 'name',
                'type' => 'type'
            ]
        ],
        'aura_test_task_typeref' => [
            'primary' => 'id',
            'map' => [
                'id' => 'id',
                'code' => 'code',
                'decode' => 'decode'
            ]
        ]

    ];

    protected function setUp()
    {
        $profiler = new Profiler();
        $this->profiler = $profiler;

        $this->query = new ConnectedQueryFactory(new QueryFactory('sqlite'));
        $this->filter = new Filter();
        $this->connection_locator = new ConnectionLocator(function () use ($profiler) {
            $pdo = new ExtendedPdo('sqlite::memory:');
            $pdo->setProfiler($profiler);
            return $pdo;
        });

        $factories = array();
        foreach ($this->data as $table_name => $info) {
            $g = new FakeGateway(
                $this->connection_locator,
                $this->query,
                $this->filter
            );
            $g->setInfo($table_name, $info['primary']);

            $m = new FakeMapper(
                $g,
                new ObjectFactory(),
                $this->filter
            );
            $m->setColsFields($info['map']);

            $factories[$table_name] = function() use ($m) {
                return $m;
            };
        }

        $this->mapper_locator = new MapperLocator($factories);

        $fixture = new SqliteFixture(
            $this->connection_locator->getWrite(),
            'aura_test_table'
        );
        $fixture->exec();

        $this->aggregate_mapper = new FakeAggregateMapper(new ObjectFactory());
        $this->aggregate_mapper->includeRelation('building', 'building.type', 'floor', 'task', 'task.type');

        $this->mediator = new DbMediator(
            $this->mapper_locator,
            new Transaction(),
            new OperationArranger(),
            new PlaceholderResolver()
        );
    }


    public function testSuccess()
    {
        var_dump($this->mediator->select($this->aggregate_mapper, array('building.id' => 1)));
        die('end');
    }
}
