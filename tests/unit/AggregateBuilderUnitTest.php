<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\AggregateBuilder;

class AggregateBuilderUnitTest extends \PHPUnit_Framework_TestCase
{
    protected $db_mediator;

    protected $aggregate_mapper_locator;

    /** @var AggregateBuilder */
    protected $aggregate_builder;

    protected $aggregate_mapper;

    protected $reflection;

    protected $row_data_arranger;

    protected function setUp()
    {
        parent::setUp();
        $this->db_mediator = \Mockery::mock('Aura\SqlMapper_Bundle\DbMediatorInterface');
        $this->aggregate_mapper_locator = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperLocator');
        $this->aggregate_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
        $this->row_data_arranger = \Mockery::mock('Aura\SqlMapper_Bundle\RowDataArranger');
        $this->aggregate_builder = new AggregateBuilder(
            $this->aggregate_mapper_locator,
            $this->db_mediator,
            $this->row_data_arranger
        );
    }

    public function tearDown()
    {
        \Mockery::close();
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
            $builder = $this->aggregate_builder;
            $this->reflection = new \ReflectionClass($builder);
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
     * This method is a quick bridge to describe what method should be called on db_mediator
     * and what it should return.
     *
     * @param string $methodName The crud method to call (create, select, update, or delete)
     *
     * @param mixed $object The parameter to pass along as parameter 2 of the crud method.
     *
     * @param string $agg_name The name of the aggregate map
     *
     * @param array|bool $results What should be returned by the db_mediator method call.
     *
     * @param int $times The number of times we'll get the aggregate mapper
     *
     */
    protected function crudMethod($methodName, $object, $agg_name, $results, $times = 1, $arranged = null)
    {
        $this->db_mediator
            ->shouldReceive($methodName)
            ->once()
            ->with($this->aggregate_mapper, $object)
            ->andReturn($results);

        $this->aggregate_mapper_locator
            ->shouldReceive('offsetExists')
            ->times($times)
            ->with($agg_name)
            ->andReturn(true);

        $this->aggregate_mapper_locator
            ->shouldReceive('offsetGet')
            ->times($times)
            ->with($agg_name)
            ->andReturn($this->aggregate_mapper);

        if ($methodName === 'select') {
            $this->row_data_arranger
                ->shouldReceive('arrangeRowData')
                ->once()
                ->with($results, $this->aggregate_mapper)
                ->andReturn($arranged);
        }
    }

    //Public methods
    public function testSelect()
    {
        $criteria = array('field' => 'value');
        $results = array(array('this' => 'is', 'a' => 'row'));
        $arranged = array(array('this' => 'is', 'an' => 'arranged', 'row' => '!'));
        $agg_name = 'MyAwesomeAggregateMapper';
        $this->crudMethod('select', $criteria, $agg_name, $results, 1, $arranged);
        $this->assertEquals($arranged, $this->aggregate_builder->select($agg_name, $criteria));
    }

    public function testUpdate()
    {
        $object = (object) array('field' => 'value');
        $results = false;
        $agg_name = 'MyAwesomeUpdateMapper';
        $this->crudMethod('update', $object, $agg_name, $results);
        $this->assertEquals($results, $this->aggregate_builder->update($agg_name, $object));
    }

    public function testCreate()
    {
        $object  = (object) array('field' => 'value');
        $results = true;
        $agg_name = 'MyAwesomeAggregateMapperForCreates';
        $this->crudMethod('create', $object, $agg_name, $results);
        $this->assertEquals($results, $this->aggregate_builder->create($agg_name, $object));
    }

    public function testDelete()
    {
        $object = (object) array('field' => 'value');
        $results = false;
        $agg_name = 'MyNotSoAwesomeAggregateMapper';
        $this->crudMethod('delete', $object, $agg_name, $results);
        $this->assertEquals($results, $this->aggregate_builder->delete($agg_name, $object));
    }

    public function testFetchObject(){
        $criteria = array('field' => 'value');
        $results = array(array('this' => 'is', 'a' => 'row'));
        $arranged = array(array('this' => 'is', 'an' => 'arranged', 'row' => '!'));
        $agg_name = 'MyAwesomeAggregateMapper';
        $object = (object) array('test' => 'object');
        $this->crudMethod('select', $criteria, $agg_name, $results, 2, $arranged);

        $this->aggregate_mapper
            ->shouldReceive('newObject')
            ->with($arranged[0])
            ->once()
            ->andReturn($object);

        $this->assertEquals($object, $this->aggregate_builder->fetchObject($agg_name, $criteria));
    }

    public function testFetchCollection(){
        $criteria = array('field' => 'value');
        $results = array(array('this' => 'is', 'a' => 'row'), array('this' => 'too is', 'a' => 'row also'));
        $agg_name = 'MyAwesomeAggregateMapper';
        $object = array(
            (object) array('test' => 'object'),
            (object) array('test' => 'otherObject')
        );

        $rowObject = $this->crudMethod('select', $criteria, $agg_name, $results, 2);

        $this->aggregate_mapper
            ->shouldReceive('newCollection')
            ->with($rowObject)
            ->once()
            ->andReturn($object);

        $this->assertEquals($object, $this->aggregate_builder->fetchCollection($agg_name, $criteria));
    }

    // Protected / internal
    public function testGetAggregateMapper()
    {
        $mappers = array(
            'one' => 'haha',
            'two' => 'monkeybrain',
            'three' => 'twiddle'
        );

        foreach($mappers as $name => $return) {
            $this->aggregate_mapper_locator
                ->shouldReceive('offsetExists')
                ->with($name)
                ->once()
                ->andReturn(true);
            $this->aggregate_mapper_locator
                ->shouldReceive('offsetGet')
                ->with($name)
                ->once()
                ->andReturn($return);
            $this->assertEquals($return, $this->aggregate_builder->getMapper($name));
        }

    }

}