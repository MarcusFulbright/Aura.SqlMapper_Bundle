<?php
namespace Aura\SqlMapper_Bundle\Test\Unit;

use Aura\SqlMapper_Bundle\AggregateMapperLocator;

class AggregateMapperLocatorUnitTest extends \PHPUnit_Framework_TestCase
{

    protected $aggregateMappers = array();

    public function setUp()
    {
        $this->aggregateMappers['account'] = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
        $this->aggregateMappers['phone'] = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
        $this->aggregateMappers['task'] = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     *
     * Instantiates a new AggregateMapperLocator with arguments as members.
     *
     * @param AggregateMapperInterface[]|AggregateMapperInterface... The members.
     *
     * @return AggregateMapperLocator
     *
     */
    protected function getMapperLocator($mappers = null)
    {
        if (is_array($mappers)) {
            return new AggregateMapperLocator($mappers);
        } else {
            return new AggregateMapperLocator(func_get_args());
        }
    }

    // Public Methods

    public function testShouldBeAbleToAddAndAccessAggregateMappers()
    {
        $aml = $this->getMapperLocator();

        foreach ($this->aggregateMappers as $name => $mapper) {
            $aml[$name] = $mapper;
            $this->assertEquals($aml[$name], $mapper);
        }
    }

    public function testShouldBeAbleToCount()
    {
        $aml = $this->getMapperLocator($this->aggregateMappers);
        $this->assertEquals(3, count($aml));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowErrorsWhenTryingToAddNonAggregateMappers()
    {
        $aml = $this->getMapperLocator();
        $aml['monkey'] = 5;
    }

    public function testShouldBeAbleToLoopThroughMappers()
    {
        $aml = $this->getMapperLocator($this->aggregateMappers);
        $i = 0;
        foreach ($aml as $name => $mapper) {
            $this->assertEquals($mapper, $aml[$name]);
            $this->assertEquals($mapper, $this->aggregateMappers[$name]);
            $i++;
        }
        $this->assertEquals(3, $i);
    }

    public function testShouldBeAbleToRemoveMappers()
    {
        $aml = $this->getMapperLocator($this->aggregateMappers);
        $this->assertEquals(3, count($aml));
        unset($aml['account']);
        $this->assertEquals(2, count($aml));
    }

    public function testOffsetExists()
    {
        $aml = $this->getMapperLocator($this->aggregateMappers);
        $this->assertTrue($aml->offsetExists('task'));
    }
}
