<?php
namespace Aura\SqlMapper_Bundle;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlQuery\QueryFactory;

class AbstractMapperTestCase extends \PHPUnit_Framework_TestCase
{
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

    protected function setUpConnection()
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
    }

    protected function setUpMFG()
    {
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
                $this->filter,
                new RowCache($g->getPrimaryCol())
            );
            $m->setColsFields($info['map']);

            $factories[$table_name] = function() use ($m) {
                return $m;
            };
        }
        $this->mapper_locator = new MapperLocator($factories);
    }

    protected function loadFixtures()
    {
        $fixture = new SqliteFixture(
            $this->connection_locator->getWrite(),
            'aura_test_table'
        );
        $fixture->exec();
    }

    protected function getAggregateMapper(
        $factory = null,
        $relations = ['building', 'building.type', 'floor', 'task', 'task.type']
    ) {
        if ($factory === null) {
            $factory = new ObjectFactory();
        }
        $this->aggregate_mapper = new FakeAggregateMapper($factory);
        call_user_func_array(array($this->aggregate_mapper, "includeRelation"), $relations);
    }

    protected function setUp()
    {
        $this->setUpConnection();
        $this->setUpMFG();
        $this->loadFixtures();
        $this->getAggregateMapper();
    }
}