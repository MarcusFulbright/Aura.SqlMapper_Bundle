<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\FakeAggregateMapper;

class AggregateMapperUnitTest extends \PHPUnit_Framework_TestCase
{
    protected $object_factory;

    protected $reflection;

    protected function setUp()
    {
        parent::setUp();
        $this->object_factory = \Mockery::mock('Aura\SqlMapper_Bundle\ObjectFactory');
        $this->reflection = new \ReflectionClass(new FakeAggregateMapper($this->object_factory));
    }

    public function tearDown() {
        \Mockery::close();
    }

    /**
     *
     * @param string ...$relation Any number of relations to include in the mapper.
     *
     * @return FakeAggregateMapper
     *
     */
    protected function getAggregateMapper()
    {
        $mapper = new FakeAggregateMapper($this->object_factory);
        $args = func_get_args();
        foreach ($args as $relation) {
            $mapper->includeRelation($relation);
        }
        return $mapper;
    }

    /**
     *
     * Lazily instantiates and returnes a cached Reflection of FakeAggregateMapper.
     *
     * @return \ReflectionClass
     *
     */
    protected function getReflection()
    {
        if (! isset($this->reflection)) {
            $mapper = $this->getAggregateMapper();
            $this->reflection = new \ReflectionClass($mapper);
        }
        return $this->reflection;
    }

    /**
     *
     * Returns an accessible ReflectionMethod by name.
     *
     * @param string $name The name of the method
     *
     * @return \ReflectionMethod
     *
     */
    protected function getProtectedMethod($name)
    {
        $method = $this->getReflection()->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     *
     * Detects whether an array is associative by checking if it only contains keys that
     * match what their numeric indices would be.
     *
     * @param $arr
     *
     * @return bool
     *
     */
    protected function isAssociative($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     *
     * This handy little function compares two arrays. The way it differs from doing a direct
     * equality check is that it doesn't care if your indexed arrays are out of order. It just
     * cares that every
     *
     * @param array $map_one
     *
     * @param array $map_two
     *
     * @return bool
     *
     */
    protected function mapsMatch(array $map_one, array $map_two) {
        if ($this->isAssociative($map_one)) {
            foreach ($map_one as $key => $value) {
                if (is_array($value)) {
                    if ($this->mapsMatch($map_one[$key], $map_two[$key]) === false) {
                        return false;
                    }
                } else {
                    if ($value !== $map_two[$key]) {
                        return false;
                    }
                }
            }
        } else {
            if (count($map_one) !== count($map_two)) {
                return false;
            }
            foreach ($map_one as $index => $value) {
                $matchFound = false;
                foreach ($map_two as $index_two => $value_two) {
                    if (is_array($value)) {
                        $matchFound = $this->mapsMatch($value, $value_two);
                    } else {
                        $matchFound = $value === $value_two;
                    }
                    if ($matchFound === true) {
                        break;
                    }
                }
                if ($matchFound === false) {
                    return false;
                }
            }
        }
        return true;
    }

    // Public methods

    public function testPropertyMap()
    {
        $mapper = $this->getAggregateMapper('building');
        $property_map = $mapper->getPropertyMap();
        $this->assertEquals(
            $property_map,
            array(
                'id' =>  'aura_test_table.id',
                'name' => 'aura_test_table.name',
                'buildingID' => 'aura_test_table.building',
                'floorID' => 'aura_test_table.floor',
                'building.id' => 'aura_test_building.id',
                'building.name' => 'aura_test_building.name',
                'building.typeCode' => 'aura_test_building.type'
            )
        );
    }

    public function testRelationMap()
    {
        $mapper = $this->getAggregateMapper('task');
        $relation_map = $mapper->getRelationMap();
        $this->assertEquals(
            $relation_map,
            array(
                'task' => array(
                    'joinProperty' => 'userID',
                    'references'   => 'id',
                    'owner'        => true,
                    'type'         => 'hasMany'
                )
            )
        );
        $this->assertEquals(1, count(array_keys($relation_map)));
    }

    public function testRelationToMapper()
    {
        $simpleMapper = $this->getAggregateMapper();
        $simpleMap = $simpleMapper->getRelationToMapper();

        $this->assertTrue(
            $this->mapsMatch(
                $simpleMap,
                array(
                    '__root' => array(
                        'mapper' => 'aura_test_table',
                        'fields' => array(
                            'id',
                            'name',
                            'building',
                            'floor'
                        ),
                        'relations' => array()
                    )
                )
            )
        );

        $complexMapper = $this->getAggregateMapper(
            'building',
            'building.type',
            'floor',
            'task',
            'task.type'
        );
        $complexMap = $complexMapper->getRelationToMapper();

        $this->assertTrue(
            $this->mapsMatch(
                $complexMap,
                array(
                    '__root' => array(
                        'mapper' => 'aura_test_table',
                        'fields' => array(
                            'id',
                            'name',
                            'building',
                            'floor'
                        ),
                        'relations' => array(
                            array(
                                'relation_name' => 'building',
                                'other_side' => 'building'
                            ),
                            array(
                                'relation_name' => 'task',
                                'other_side' => 'task'
                            ),
                            array(
                                'relation_name' => 'floor',
                                'other_side' => 'floor'
                            )
                        )
                    ),
                    'building' => array(
                        'mapper' => 'aura_test_building',
                        'fields' => array(
                            'id',
                            'name',
                            'type'
                        ),
                        'relations' => array(
                            array(
                                'relation_name' => 'building',
                                'other_side' => '__root'
                            ),
                            array(
                                'relation_name' => 'building.type',
                                'other_side' => 'building.type'
                            )
                        )
                    ),
                    'building.type' => array(
                        'mapper' => 'aura_test_building_typeref',
                        'fields' => array(
                            'id',
                            'code',
                            'decode'
                        ),
                        'relations' => array(
                            array(
                                'relation_name' => 'building.type',
                                'other_side' => 'building'
                            )
                        )
                    ),
                    'floor' => array(
                        'mapper' => 'aura_test_floor',
                        'fields' => array(
                            'id',
                            'name'
                        ),
                        'relations' => array(
                            array(
                                'relation_name' => 'floor',
                                'other_side' => '__root'
                            )
                        )
                    ),
                    'task' => array(
                        'mapper' => 'aura_test_task',
                        'fields' => array(
                            'id',
                            'userid',
                            'name',
                            'type'
                        ),
                        'relations' => array(
                            array(
                                'relation_name' => 'task',
                                'other_side' => '__root'
                            ),
                            array(
                                'relation_name' => 'task.type',
                                'other_side' => 'task.type'
                            )
                        )
                    ),
                    'task.type' => array(
                        'mapper' => 'aura_test_task_typeref',
                        'fields' => array(
                            'code',
                            'decode'
                        ),
                        'relations' => array(
                            array(
                                'relation_name' => 'task.type',
                                'other_side' => 'task'
                            )
                        )
                    )
                )
            )
        );
    }

    public function testNewCollection()
    {
        $data = array('monkey' => 'tail');
        $return = 'testReturn';
        $this->object_factory
            ->shouldReceive('newCollection')
            ->once()
            ->with($data)
            ->andReturn($return);
        $mapper = $this->getAggregateMapper();
        $this->assertEquals($return, $mapper->newCollection($data));
    }

    public function testNewObject()
    {
        $data = array('monkey' => 'tail');
        $return = 'testReturn';
        $this->object_factory
            ->shouldReceive('newObject')
            ->once()
            ->with($data)
            ->andReturn($return);
        $mapper = $this->getAggregateMapper();
        $this->assertEquals($return, $mapper->newObject($data));
    }

    // Protected / Internals
    public function testSplitStringOnLast()
    {
        $mapper = $this->getAggregateMapper();
        $method = $this->getProtectedMethod('splitStringOnLast');

        $test = array(
            'test.one' => array('test', 'one'),
            'single' => array('', 'single'),
            'this.has.lots.of.them' => array('this.has.lots.of', 'them')
        );

        foreach ($test as $string => $result) {
            $this->assertEquals($result, $method->invokeArgs($mapper, array('.', $string)));
        }

        $this->assertEquals(
            array('prepended', 'field'),
            $method->invokeArgs($mapper, array('.', 'field', 'prepended'))
        );

        $this->assertEquals(
            array('notPrepended', 'field'),
            $method->invokeArgs($mapper, array('.', 'notPrepended.field', 'prepended'))
        );
    }

    public function testSeparatePropertyFromAddress()
    {
        $mapper = $this->getAggregateMapper();
        $method = $this->getProtectedMethod('separatePropertyFromAddress');

        $test = array(
            'test.one' => array('address' => 'test', 'property' => 'one'),
            'single' => array('address' => '__root', 'property' => 'single'),
            'this.has.lots.of.them' => array('address' => 'this.has.lots.of', 'property' => 'them')
        );

        foreach ($test as $string => $result) {
            $this->assertEquals((object) $result, $method->invokeArgs($mapper, array($string)));
        }
    }

    public function testSeparateMapperFromField()
    {
        $mapper = $this->getAggregateMapper();
        $method = $this->getProtectedMethod('separateMapperFromField');

        $test = array(
            'test.one' => array('mapper' => 'test', 'field' => 'one'),
            'solemn.monkey' => array('mapper' => 'solemn', 'field' => 'monkey'),
            'this.should.still.be.them' => array('mapper' => 'this.should.still.be', 'field' => 'them')
        );

        foreach ($test as $string => $result) {
            $this->assertEquals((object) $result, $method->invokeArgs($mapper, array($string)));
        }
    }

    /**
     * @expectedException Exception
     */
    public function testSeparateMapperFromFieldThrowsWhenProvidedNoMapper()
    {
        $mapper = $this->getAggregateMapper();
        $method = $this->getProtectedMethod('separateMapperFromField');
        $method->invokeArgs($mapper, array('throwMeSomeError'));
    }

    public function testMakeRelationToMapper()
    {
        $mapper = $this->getAggregateMapper();
        $method = $this->getProtectedMethod('makeRelationToMapper');

        $property_map = array(
            'propertyOne' => 'mapper.propOne',
            'propertyTwo' => 'mapper.propTwo',
            'embedded.propertyOne' => 'secondMapper.embeddedPropOne',
            'embedded.propertyTwo' => 'secondMapper.embeddedPropTwo'
        );
        $relation_map = array(
            'embedded' => array(
                'joinProperty' => 'propertyTwo',
                'reference' => 'propertyTwo',
                'owner' => true,
                'type' => 'hasOne'
            )
        );
        $should_equal = array(
            '__root' => array(
                'mapper' => 'mapper',
                'fields' => array(
                    'propOne',
                    'propTwo'
                ),
                'relations' => array(
                    array(
                        'relation_name' => 'embedded',
                        'other_side' => 'embedded1'
                    )
                )
            ),
            'embedded' => array(
                'mapper' => 'secondMapper',
                'fields' => array(
                    'embeddedPropOne',
                    'embeddedPropTwo'
                ),
                'relations' => array(
                    array(
                        'relation_name' => 'embedded',
                        'other_side' => '__root'
                    )
                )
            )
        );

        $this->assertTrue(
            $this->mapsMatch(
                $should_equal,
                $method->invokeArgs($mapper, array($property_map, $relation_map))
            )
        );
    }
}
