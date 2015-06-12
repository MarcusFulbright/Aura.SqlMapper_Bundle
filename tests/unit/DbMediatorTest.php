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
                $this->filter,
                new RowCache($g->getPrimaryCol())
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
            new PlaceholderResolver(),
            new RowDataExtractor()
        );
    }

    protected function createStdClass(array $props)
    {
        $obj = new \stdClass();
        foreach ($props as $prop => $value) {
            $obj->$prop = $value;
        }
        return $obj;
    }

    public function testSelectWithWhereLeafOwns()
    {
        $expected = array(
            '__root' => array($this->createStdClass(array(
                'id' => 2,
                'name' => 'Betty',
                'building' => 1,
                'floor' => 2
            ))),
            'building' => array($this->createStdClass(array(
                'id' => 1,
                'name' => 'Bower Street',
                'type' => 'NP'
            ))),
            'building.type' => array($this->createStdClass(array(
                'id' => 1,
                'code' => 'NP',
                'decode' => 'Non-Profit'
            ))),
            'floor' => array($this->createStdClass(array(
                'id' => 2,
                'name' => 'Accounting'
            ))),
            'task' => array(
                $this->createStdClass(array(
                    'id' => 3,
                    'userid' => 2,
                    'name' => 'Budget Planning',
                    'type' => 'F'
                )),
                $this->createStdClass(array(
                    'id' => 4,
                    'userid' => 2,
                    'name' => 'Budget Meeting',
                    'type' => 'M'
                ))
            ),
            'task.type' => array(
                $this->createStdClass(array(
                    'id' => 3,
                    'code' => 'F',
                    'decode' => 'Financials'
                )),
                $this->createStdClass(array(
                    'id' => 4,
                    'code' => 'M',
                    'decode' => 'Meeting'
                ))
            )
        );
        $this->assertEquals(
            $expected,
            $this->mediator->select($this->aggregate_mapper, array('task.type.code' => 'F'))
        );
    }

    public function testSelectWhereRootOwns()
    {
        $expected = array(
            '__root' => array(
                $this->createStdClass(array(
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3'
                )),
                $this->createStdClass(array(
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3'
                )),
                $this->createStdClass(array(
                    'id' => '9',
                    'name' => 'Ione',
                    'building' => '2',
                    'floor' => '3'
                )),
                $this->createStdClass(array(
                    'id' => '12',
                    'name' => 'Lana',
                    'building' => '2',
                    'floor' => 3
                ))
            ),
            'building' => array(
                $this->createStdClass(array(
                    'id' => '1',
                    'name' => 'Bower Street',
                    'type' => 'NP'
                )),
                $this->createStdClass(array(
                    'id' => '2',
                    'name' => 'Dominion',
                    'type' => 'P'
                ))
            ),
            'building.type' => array(
                $this->createStdClass(array(
                    'id' => 1,
                    'code' => 'NP',
                    'decode' => 'Non-Profit'
                )),
                $this->createStdClass(array(
                    'id' => 2,
                    'code' => 'P',
                    'decode' => 'For Profit'
                ))
            ),
            'floor' => array(
                $this->createStdClass(array(
                    'id' => 3,
                    'name' => 'Marketing'
                ))
            ),
            'task' => array(),
            'task.type' => array()
        );
        $this->assertEquals($expected, $this->mediator->select($this->aggregate_mapper, array('floor.id' => '3')));
    }

    public function testSelectCriteriaOnRoot()
    {
        $criteria = array('__root.id' => 3);
        $expected = array(
            '__root' => array($this->createStdClass(array(
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3'
            ))),
            'building' => array($this->createStdClass(array(
                'id' => '1',
                'name' => 'Bower Street',
                'type' => 'NP'
            ))),
            'building.type' => array($this->createStdClass(array(
                'id' => 1,
                'code' => 'NP',
                'decode' => 'Non-Profit'
            ))),
            'floor' => array($this->createStdClass(array(
                'id' => 3,
                'name' => 'Marketing'
            ))),
            'task' => array(),
            'task.type' => array(),
        );
        $this->assertEquals($expected, $this->mediator->select($this->aggregate_mapper, $criteria));
    }

    public function testSelectNoCriteria()
    {
        $results = $this->mediator->select($this->aggregate_mapper);
        $this->assertArrayHasKey('__root', $results);
        $this->assertCount(12, $results['__root']);
        $this->assertArrayHasKey('building', $results);
        $this->assertCount(2, $results['building']);
        $this->assertArrayHasKey('building.type', $results);
        $this->assertCount(2, $results['building.type']);
        $this->assertArrayHasKey('floor', $results);
        $this->assertCount(3, $results['floor']);
        $this->assertArrayHasKey('task', $results);
        $this->assertCount(6, $results['task']);
        $this->assertArrayHasKey('task.type', $results);
        $this->assertCount(4, $results['task.type']);
    }

    public function testCreateOnlyRootNew()
    {
        $obj = (object)array(
            'id' => null,
            'name' => 'Missy',
        );
        //fetch data from the DB so that the gateway caches it.
        $fetched = $this->mediator->select($this->aggregate_mapper, array('id' => 1));
        $obj->building = $fetched['building'][0];
        $obj->building->type = $fetched['building.type'][0];
        $obj->floor = $fetched['floor'][0];
        $obj->task = [];

        $expected = clone($obj);
        $expected->id = '13';
        $this->assertEquals($expected, $this->mediator->create($this->aggregate_mapper, $obj));
    }

    public function testCreateNewRootNewLeaf()
    {
        $obj = (object)array(
            'id' => null,
            'name' => 'Missy',
            'floor' => (object) array(
                'id' => null,
                'name' => 'Business Intelligence'
            )
        );
        //fetch data from the DB so that the gateway caches it.
        $fetched = $this->mediator->select($this->aggregate_mapper, array('id' => 1));
        $obj->building = $fetched['building'][0];
        $obj->building->type = $fetched['building.type'][0];
        $obj->task = [];

        $expected = clone($obj);
        $expected->id = '13';
        $expected->floor->id = 4;
        $this->assertEquals($expected, $this->mediator->create($this->aggregate_mapper, $obj));
    }

    public function testCreateExistingRootThrowsError()
    {
        $fetched = $this->mediator->select($this->aggregate_mapper, array('id' => 1));
        $obj = (object) $fetched['__root'][0];
        $obj->floor = (object) array(
            'id' => null,
            'name' => 'Not Going Into The DB'
        );
        $obj->building = (object)$fetched['building'][0];
        $obj->building->type = (object)$fetched['building.type'][0];
        $obj->task = [];

        $this->setExpectedException('Aura\SqlMapper_Bundle\Exception\DbOperationException');
        $this->mediator->create($this->aggregate_mapper, $obj);
    }

    public function testUpdateWithRootChangeAndNewLeaf()
    {
        $fetched = $this->mediator->select($this->aggregate_mapper, array('id' => 1));
        $obj = (object) $fetched['__root'][0];
        $obj->name = 'Altered';
        $obj->floor = (object) array(
            'id' => null,
            'name' => 'Brand New Unique Floor Name'
        );
        $obj->building = (object)$fetched['building'][0];
        $obj->building->type = (object)$fetched['building.type'][0];
        $obj->task = [];
        $expected = clone($obj);
        $expected->floor->id = '4';
        $this->assertEquals($expected, $this->mediator->update($this->aggregate_mapper, $obj));
    }
}
