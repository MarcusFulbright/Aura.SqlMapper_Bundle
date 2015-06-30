<?php
namespace Aura\SqlMapper_Bundle;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlMapper_Bundle\MapperLocator;

class AggregateBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $aggregate_mapper;
    protected $aggregate_mapper_locator;
    protected $db_mediator;
    protected $aggregate_builder;
    protected $row_data_arranger;

    protected $connections;
    protected $connection_locator;
    protected $profiler;
    protected $query;
    protected $object_factory;
    protected $filter;
    protected $mapper_locator;

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

        $this->mapper_locator = new MapperLocator($factories);

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
            $this->mapper_locator,
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
    }

    public function testGetCollectionNoCriteria()
    {
        $results = $this->aggregate_builder->getCollection('employee');
        $expected = $this->getFullExpectedResults();

        $this->assertEquals(
            $expected,
            $results
        );
    }

    public function testGetCollectionWithBasicCriteria()
    {
        $results = $this->aggregate_builder->getCollection(
            'employee',
            array('id' => 4)
        );
        $expected = $this->getExpectedResults(4);

        $this->assertEquals($expected, $results);
    }

    public function testGetCollectionWithNestedCriteria()
    {
        $results = $this->aggregate_builder->getCollection(
            'employee',
            array('building.id' => 1)
        );
        $expected = $this->getExpectedResults(array(1, 2, 3, 4, 5, 6));
        $this->assertEquals($expected, $results);
    }

    public function testGetCollectionWithMoreNestedCriteria()
    {
        $results = $this->aggregate_builder->getCollection(
            'employee',
            array('task.type.code' => 'M')
        );
        $expected = $this->getExpectedResults(array(2, 5, 8));
        $this->assertEquals($expected, $results);
    }

    public function testGetObject()
    {
        $betty = $this->aggregate_builder->getObject(
            'employee',
            array('id' => 2)
        );
        $expected = $this->getExpectedResults(2);

        $this->assertEquals($expected[0], $betty);
    }

    public function testUpdateCycle()
    {
        $betty = $this->aggregate_builder->getObject(
            'employee',
            array('id' => 2)
        );

        $betty->name = 'Beatrice';
        $this->aggregate_builder->update('employee', $betty);

        $bettyAgain = $this->aggregate_builder->getObject(
            'employee',
            array('id' => 2)
        );
        $expected = $this->getExpectedResults(2);
        $expected[0]->name = 'Beatrice';

        $this->assertEquals($expected[0], $bettyAgain);
    }

    public function testDeleteCycle()
    {
        $betty = $this->aggregate_builder->getObject(
            'employee',
            array('id' => 2)
        );

        $this->aggregate_builder->delete('employee', $betty);

        $bettyAgain = $this->aggregate_builder->getObject(
            'employee',
            array('id' => 2)
        );

        $this->assertEquals(false, $bettyAgain);

    }

    public function testCreate()
    {
        $jackie = (object) array(
            'id' => null,
            'name' => 'Jackie',
            'building' => (object) array(
                'id' => 1,
                'name' => 'Bower Street',
                'type' => (object) array(
                    'id' => 1,
                    'code' => 'NP',
                    'decode' => 'Non-Profit'
                )
            ),
            'floor' => (object) array(
                'id' => '1',
                'name' => 'Reception'
            ),
            'task' => array(
                (object) array(
                    'id' => null,
                    'name' => 'Review Calendar',
                    'type' => (object) array(
                        'id' => '1',
                        'code' => 'S',
                        'decode' => 'Scheduling'
                    )
                )
            )
        );
        $this->assertEquals(true, $this->aggregate_builder->create('employee', $jackie));
        $this->assertEquals(13, $jackie->id);

    }

    /**
     *
     * Grabs expected results by id from the full result array.
     *
     * @param array|int $id The id(s) to include
     *
     * @return array The matching results entries.
     *
     */
    protected function getExpectedResults($id)
    {
        $id = is_array($id) ? $id : array($id);
        $output = array();
        foreach ($this->getFullExpectedResults() as $index => $object) {
            if (in_array($object->id, $id)) {
                $output[] = $object;
            }
        }
        return $output;
    }

    /**
     *
     * Creates and returns the full results set from the sqlite fixture.
     *
     * @return array
     *
     */
    protected function getFullExpectedResults()
    {
        return array(
            (object) array(
                'id' => '1',
                'name' => 'Anna',
                'building' => (object) array(
                    'id' => 1,
                    'name' => 'Bower Street',
                    'type' => (object) array(
                        'id' => 1,
                        'code' => 'NP',
                        'decode' => 'Non-Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '1',
                    'name' => 'Reception'
                ),
                'task' => array(
                    (object) array(
                        'id' => '1',
                        'name' => 'Manage Calendar',
                        'type' => (object) array(
                            'id' => '1',
                            'code' => 'S',
                            'decode' => 'Scheduling'
                        )
                    ),
                    (object) array(
                        'id' => '2',
                        'name' => 'Plan Potluck',
                        'type' => (object) array(
                            'id' => '2',
                            'code' => 'P',
                            'decode' => 'Party / Event'
                        )
                    )
                )
            ),

            (object) array(
                'id' => '2',
                'name' => 'Betty',
                'building' => (object) array(
                    'id' => 1,
                    'name' => 'Bower Street',
                    'type' => (object) array(
                        'id' => 1,
                        'code' => 'NP',
                        'decode' => 'Non-Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '2',
                    'name' => 'Accounting'
                ),
                'task' => array(
                    (object) array(
                        'id' => '3',
                        'name' => 'Budget Planning',
                        'type' => (object) array(
                            'id' => '3',
                            'code' => 'F',
                            'decode' => 'Financials'
                        )
                    ),
                    (object) array(
                        'id' => '4',
                        'name' => 'Budget Meeting',
                        'type' => (object) array(
                            'id' => '4',
                            'code' => 'M',
                            'decode' => 'Meeting'
                        )
                    )
                )
            ),

            (object) array(
                'id' => '3',
                'name' => 'Clara',
                'building' => (object) array(
                    'id' => 1,
                    'name' => 'Bower Street',
                    'type' => (object) array(
                        'id' => 1,
                        'code' => 'NP',
                        'decode' => 'Non-Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '3',
                    'name' => 'Marketing'
                ),
                'task' => array()
            ),

            (object) array(
                'id' => '4',
                'name' => 'Donna',
                'building' => (object) array(
                    'id' => 1,
                    'name' => 'Bower Street',
                    'type' => (object) array(
                        'id' => 1,
                        'code' => 'NP',
                        'decode' => 'Non-Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '1',
                    'name' => 'Reception'
                ),
                'task' => array()
            ),

            (object) array(
                'id' => '5',
                'name' => 'Edna',
                'building' => (object) array(
                    'id' => 1,
                    'name' => 'Bower Street',
                    'type' => (object) array(
                        'id' => 1,
                        'code' => 'NP',
                        'decode' => 'Non-Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '2',
                    'name' => 'Accounting'
                ),
                'task' => array(
                    (object) array(
                        'id' => '5',
                        'name' => 'Budget Meeting',
                        'type' => (object) array(
                            'id' => '4',
                            'code' => 'M',
                            'decode' => 'Meeting'
                        )
                    )
                )
            ),

            (object) array(
                'id' => '6',
                'name' => 'Fiona',
                'building' => (object) array(
                    'id' => 1,
                    'name' => 'Bower Street',
                    'type' => (object) array(
                        'id' => 1,
                        'code' => 'NP',
                        'decode' => 'Non-Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '3',
                    'name' => 'Marketing'
                ),
                'task' => array()
            ),

            (object) array(
                'id' => '7',
                'name' => 'Gina',
                'building' => null,
                'floor' => (object) array(
                    'id' => '1',
                    'name' => 'Reception'
                ),
                'task' => array()
            ),

            (object) array(
                'id' => '8',
                'name' => 'Hanna',
                'building' => (object) array(
                    'id' => 2,
                    'name' => 'Dominion',
                    'type' => (object) array(
                        'id' => 2,
                        'code' => 'P',
                        'decode' => 'For Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '2',
                    'name' => 'Accounting'
                ),
                'task' => array(
                    (object) array(
                        'id' => '6',
                        'name' => 'Budget Meeting',
                        'type' => (object) array(
                            'id' => '4',
                            'code' => 'M',
                            'decode' => 'Meeting'
                        )
                    )
                )
            ),

            (object) array(
                'id' => '9',
                'name' => 'Ione',
                'building' => (object) array(
                    'id' => 2,
                    'name' => 'Dominion',
                    'type' => (object) array(
                        'id' => 2,
                        'code' => 'P',
                        'decode' => 'For Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '3',
                    'name' => 'Marketing'
                ),
                'task' => array()
            ),

            (object) array(
                'id' => '10',
                'name' => 'Julia',
                'building' => (object) array(
                    'id' => 2,
                    'name' => 'Dominion',
                    'type' => (object) array(
                        'id' => 2,
                        'code' => 'P',
                        'decode' => 'For Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '1',
                    'name' => 'Reception'
                ),
                'task' => array()
            ),

            (object) array(
                'id' => '11',
                'name' => 'Kara',
                'building' => (object) array(
                    'id' => 2,
                    'name' => 'Dominion',
                    'type' => (object) array(
                        'id' => 2,
                        'code' => 'P',
                        'decode' => 'For Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '2',
                    'name' => 'Accounting'
                ),
                'task' => array()
            ),

            (object) array(
                'id' => '12',
                'name' => 'Lana',
                'building' => (object) array(
                    'id' => 2,
                    'name' => 'Dominion',
                    'type' => (object) array(
                        'id' => 2,
                        'code' => 'P',
                        'decode' => 'For Profit'
                    )
                ),
                'floor' => (object) array(
                    'id' => '3',
                    'name' => 'Marketing'
                ),
                'task' => array()
            )
        );
    }
}
