<?php
namespace Aura\SqlMapper_Bundle;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlQuery\QueryFactory;

class DomainObjectBuilderTest extends \PHPUnit_Framework_TestCase
{
    use DbResultUtil;

    /** @var FakeAggregateMapper */
    protected $aggregate_mapper;

    /** @var AggregateMapperLocator */
    protected $aggregate_mapper_locator;

    /** @var DbMediator */
    protected $db_mediator;

    /** @var AggregateBuilder */
    protected $aggregate_builder;

    /** @var RowDataArranger */
    protected $row_data_arranger;

    /** @var ConnectionLocator */
    protected $connection_locator;

    /** @var Profiler */
    protected $profiler;

    /** @var ConnectedQueryFactory */
    protected $query;

    /** @var Filter */
    protected $filter;

    /** @var  RowMapperLocator */
    protected $mapper_locator;

    /** @var RowObjectBuilder */
    protected $row_builder;

    /** @var DomainObjectBuilder */
    protected $domain_builder;

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
        parent::setUp();

        $profiler = new Profiler;
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
                $this->filter,
                new RowCache($g->getPrimaryCol())
            );
            $m->setColsFields($info['map']);

            $factories[$table_name] = function() use ($m) {
                return $m;
            };
        }

        $this->mapper_locator = new RowMapperLocator($factories);

        $fixture = new SqliteFixture(
            $this->connection_locator->getWrite(),
            'aura_test_table'
        );
        $fixture->exec();

        $this->aggregate_mapper = new FakeAggregateMapper(new AggregateObjectFactory());
        $this->aggregate_mapper->includeRelation(
            'building',
            'building.type',
            'floor',
            'task',
            'task.type'
        );
        $this->aggregate_mapper_locator = new AggregateMapperLocator(
            array(
                'employee' => $this->aggregate_mapper
            )
        );

        $this->db_mediator = new DbMediator(
            new RowObjectBuilder($this->mapper_locator),
            new OperationArranger(),
            new PlaceholderResolver(),
            new RowDataExtractor(),
            new OperationCallbackFactory()
        );

        $this->row_data_arranger = new RowDataArranger();

        $this->aggregate_builder = new AggregateBuilder(
            $this->aggregate_mapper_locator,
            $this->db_mediator,
            $this->row_data_arranger
        );

        $this->row_builder = new RowObjectBuilder($this->mapper_locator);

        $this->domain_builder = new DomainObjectBuilder($this->aggregate_builder, $this->row_builder);
    }

    public function testFetchCollectionAggregate()
    {
        $results = $this->domain_builder->fetchCollection('employee', ['id' => '2']);
        $this->assertEquals([$this->formatRecordToObject($this->getBetty())], $results);
    }

    public function testFetchCollectionRow()
    {
        $results = $this->domain_builder->fetchCollection('aura_test_task', ['userid' => '2']);
        $this->assertEquals($this->resolveTasks(2), $results);
    }

    public function testFetchObjectAggregate()
    {
        $results = $this->domain_builder->fetchObject('employee', ['id' => '2']);
        $this->assertEquals($this->formatRecordToObject($this->getBetty()), $results);
    }

    public function testFetchObjectRow()
    {
        $result = $this->domain_builder->fetchObject('aura_test_building', ['id' => '1']);
        $this->assertEquals($this->getBowerStreetBuilding(), $result);
    }

    public function testSelectAggregate()
    {
        $result = $this->domain_builder->select('employee', ['id' => 2]);
        $expected = $this->row_data_arranger->arrangeRowData($this->getBetty(), $this->aggregate_mapper);
        $this->assertEquals($expected, $result);
    }

    public function testSelectRow()
    {
        $result = $this->domain_builder->select('aura_test_building', ['id' => 1]);
        $expected = [(array)$this->getBowerStreetBuilding()];
        $this->assertEquals($expected, $result);
    }

    public function testUpdateAggregate()
    {
        $obj = $this->formatRecordToObject($this->getBetty());
        $this->assertTrue($this->domain_builder->update('employee', $obj));
    }

    public function testUpdateRow()
    {
        $row = $this->getBowerStreetBuilding();
        $this->assertTrue($this->domain_builder->update('aura_test_building', $row));
    }

    public function testDeleteAggregate()
    {
        $obj = $this->formatRecordToObject($this->getBetty());
        $this->assertTrue($this->domain_builder->delete('employee', $obj));
    }

    public function testDeleteRow()
    {
        $row = $this->getBowerStreetBuilding();
        $this->assertTrue($this->domain_builder->delete('aura_test_building', $row));
    }
}
