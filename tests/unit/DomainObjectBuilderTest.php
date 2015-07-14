<?php
namespace Aura\SqlMapper_Bundle\Tests\Unit;

use Aura\SqlMapper_Bundle\DomainObjectBuilder;
use Mockery\MockInterface;

class DomainObjectBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var MockInterface */
    protected $aggregate_builder;

    /** @var MockInterface */
    protected $row_builder;

    /** @var DomainObjectBuilder */
    protected $domain_builder;

    /** @var MockInterface */
    protected $aggregate_mapper;

    /** @var MockInterface */
    protected $row_mapper;

    public function setUp()
    {
        $this->aggregate_builder = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateBuilder');
        $this->row_builder = \Mockery::mock('Aura\SqlMapper_Bundle\RowObjectBuilder');
        $this->domain_builder = new DomainObjectBuilder($this->aggregate_builder, $this->row_builder);
        $this->aggregate_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AbstractAggregateMapper');
        $this->row_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AbstractRowMapper');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    protected function configureAggregateBuilder($return_mapper = false)
    {
        if ($return_mapper) {
            $this
                ->aggregate_builder
                ->shouldReceive('getMapper')
                ->once()
                ->with('aggregate_mapper')
                ->andReturn($this->aggregate_mapper);
        } elseif ($return_mapper === false) {
             $this
                ->aggregate_builder
                ->shouldReceive('getMapper')
                ->once()
                ->andReturn(false);
        } else {
            $this
                ->aggregate_builder
                ->shouldReceive('getMapper')
                ->never();
        }
    }

    protected function configureRowBuilder($return_mapper = false)
    {
        if ($return_mapper) {
            $this
                ->row_builder
                ->shouldReceive('getMapper')
                ->once()
                ->with('row_mapper')
                ->andReturn($this->row_mapper);
        } elseif ($return_mapper === false) {
             $this
                ->row_builder
                ->shouldReceive('getMapper')
                ->once()
                 ->andReturn(false);
        } else {
            $this
                ->row_builder
                ->shouldReceive('getMapper')
                ->never();
        }
    }

    public function testGetBuilderAggregate()
    {
        $this->configureAggregateBuilder(true);
        $this->configureRowBuilder(null);
        $this->assertEquals($this->aggregate_builder, $this->domain_builder->getBuilder('aggregate_mapper'));
    }

    public function testGetBuilderRow()
    {
        $this->configureAggregateBuilder(false);
        $this->configureRowBuilder(true);
        $this->assertEquals($this->row_builder, $this->domain_builder->getBuilder('row_mapper'));
    }

    public function testGeBuilderException()
    {
        $this->setExpectedException(
            'Aura\SqlMapper_Bundle\Exception\NoSuchMapper',
            'wrong_mapper is not defined'
        );
        $this->configureAggregateBuilder(false);
        $this->configureRowBuilder(false);
        $this->domain_builder->getBuilder('wrong_mapper');
    }

    public function testGetMapperAggregate()
    {
        $this->configureAggregateBuilder(true);
        $this->configureRowBuilder(null);
        $this->assertEquals($this->aggregate_mapper, $this->domain_builder->getMapper('aggregate_mapper'));
    }

    public function testGetMapperRow()
    {
        $this->configureAggregateBuilder(false);
        $this->configureRowBuilder(true);
        $this->assertEquals($this->row_mapper, $this->domain_builder->getMapper('row_mapper'));
    }

    public function testGetMapperException()
    {
        $this->setExpectedException(
            'Aura\SqlMapper_Bundle\Exception\NoSuchMapper',
            'wrong_mapper is not defined'
        );
        $this->configureAggregateBuilder(false);
        $this->configureRowBuilder(false);
        $this->domain_builder->getMapper('wrong_mapper');
    }

    public function testFetchCollection()
    {
        $criteria = ['id' => 2];
        $this->configureAggregateBuilder(true);
        $this->configureRowBuilder(null);
        $this
            ->aggregate_builder
            ->shouldReceive('fetchCollection')
            ->once()
            ->with('aggregate_mapper', $criteria)
            ->andReturn(true);

        $this->assertTrue($this->domain_builder->fetchCollection('aggregate_mapper', $criteria));
    }

    public function testFetchObject()
    {
        $criteria = ['id' => 2];
        $this->configureAggregateBuilder(true);
        $this->configureRowBuilder(null);
        $this
            ->aggregate_builder
            ->shouldReceive('fetchObject')
            ->once()
            ->with('aggregate_mapper', $criteria)
            ->andReturn(true);
        $this->assertTrue($this->domain_builder->fetchObject('aggregate_mapper', $criteria));
    }

    public function testSelect()
    {
        $criteria = ['id' => 2];
        $this->configureAggregateBuilder(true);
        $this->configureRowBuilder(null);
        $this
            ->aggregate_builder
            ->shouldReceive('select')
            ->once()
            ->with('aggregate_mapper', $criteria)
            ->andReturn(true);
        $this->assertTrue($this->domain_builder->select('aggregate_mapper', $criteria));
    }

    public function testUpdate()
    {
        $object = new \stdClass();
        $this->configureAggregateBuilder(true);
        $this->configureRowBuilder(null);
        $this
            ->aggregate_builder
            ->shouldReceive('update')
            ->once()
            ->with('aggregate_mapper', $object)
            ->andReturn(true);
        $this->domain_builder->update('aggregate_mapper', $object);
    }

    public function testDelete()
    {
        $object = new \stdClass();
        $this->configureAggregateBuilder(true);
        $this->configureRowBuilder(null);
        $this
            ->aggregate_builder
            ->shouldReceive('delete')
            ->once()
            ->with('aggregate_mapper', $object)
            ->andReturn(true);
        $this->domain_builder->delete('aggregate_mapper', $object);
    }
}
