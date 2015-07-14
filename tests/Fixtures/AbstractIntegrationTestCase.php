<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperLocator;
use Aura\SqlMapper_Bundle\Aggregate\AggregateRepository;
use Aura\SqlMapper_Bundle\Entity\EntityCache;
use Aura\SqlMapper_Bundle\Entity\EntityMapperLocator;
use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\EntityMediation\EntityArranger;
use Aura\SqlMapper_Bundle\EntityMediation\EntityExtractor;
use Aura\SqlMapper_Bundle\EntityMediation\EntityMediator;
use Aura\SqlMapper_Bundle\EntityMediation\OperationArranger;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver;
use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlQuery\QueryFactory;

class AbstractIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    use Assertions;

    use DbResultUtil;

    /** @var  ConnectionLocator */
    protected $connection_locator;

    protected $query;

    /** @var Filter */
    protected $filter;

    /** @var Profiler */
    protected $profiler;

    /** @var EntityMapperLocator */
    protected $mapper_locator;

    /** @var  AggregateMapperInterface */
    protected $aggregate_mapper;

    /** @var EntityRepository */
    protected $entity_repository;

    /** @var array */
    protected $gateways;

    /** @var array */
    protected $factories;

    /** @var AggregateMapperLocator */
    protected $aggregate_mapper_locator;

    /** @var AggregateRepository */
    protected $aggregate_repository;

    /** @var  EntityMediator */
    protected $mediator;

    /** @var EntityArranger */
    protected $entity_arranger;

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

    protected function setUpEntities()
    {
        $this->setUpConnection();
        $this->setUpMFG();
    }

    protected function setUpAggregates()
    {
        $this->setUpEntities();
        $this->setUpEntityMediator();
        $this->setUpAggregateMapper();
        $this->setUpAggregateRepository();
    }

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

            $m = new FakeEntityMapper(
                $g,
                new FakeEntityFactory(),
                $this->filter,
                new EntityCache($g->getPrimaryCol())
            );
            $m->setColsFields($info['map']);

            $factories[$table_name] = function() use ($m) {
                return $m;
            };

            $this->gateways[$table_name] = $g;
        }
        $this->factories = $factories;
        $this->mapper_locator = new EntityMapperLocator($factories);
        $this->entity_repository = new EntityRepository($this->mapper_locator);
    }

    protected function loadFixtures()
    {
        $fixture = new SqliteFixture(
            $this->connection_locator->getWrite(),
            'aura_test_table'
        );
        $fixture->exec();
    }

    protected function setUpAggregateMapper(
        $factory = null,
        $relations = ['building', 'building.type', 'floor', 'task', 'task.type']
    ) {
        if ($factory === null) {
            $factory = new FakeEntityFactory();
        }
        $this->aggregate_mapper = new FakeAggregateMapper($factory);
        call_user_func_array(array($this->aggregate_mapper, "includeRelation"), $relations);
        $this->aggregate_mapper_locator = new AggregateMapperLocator(['employee' => $this->aggregate_mapper]);
    }

    protected function setUpEntityMediator()
    {
        $this->mediator = new EntityMediator(
            $this->entity_repository,
            new OperationArranger(),
            new PlaceholderResolver(),
            new EntityExtractor(),
            new OperationCallbackFactory()
        );
    }

    protected function setUpAggregateRepository()
    {
        $this->entity_arranger = new EntityArranger();
        $this->aggregate_repository = new AggregateRepository(
            $this->aggregate_mapper_locator,
            $this->mediator,
            $this->entity_arranger
        );
    }
}