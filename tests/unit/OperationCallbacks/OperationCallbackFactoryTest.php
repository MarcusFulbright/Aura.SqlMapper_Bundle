<?php
namespace Aura\SqlMapper_Bundle\unit;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;
use Mockery\MockInterface;

/**
 * Test OperationCallbackFactoryTest
 * @package Aura\SqlMapper_Bundle\unit
 */
class OperationCallbackFactoryTest extends \PHPUnit_Framework_TestCase 
{
    /** @var OperationCallbackFactory */
    protected $factory;

    /** @var MockInterface */
    protected $row_builder;

    /** @var MockInterface */
    protected $mapper;

    /** @var MockInterface */
    protected $arranger;

    /** @var MockInterface */
    protected $resolver;

    public function setUp()
    {
        $this->factory = new OperationCallbackFactory();

        $this->row_builder = \Mockery::mock('Aura\SqlMapper_Bundle\RowObjectBuilder');
        $this->mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AbstractAggregateMapper');
        $this->arranger = \MOckery::mock('Aura\SqlMapper_Bundle\OperationArranger');
        $this->resolver = \Mockery::mock('Aura\SqlMapper_Bundle\PlaceholderResolver');
    }

    public function testGetTransaction()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\Transaction',
            $this->factory->getTransaction()
        );
    }

    public function testGetCommitCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\CommitCallback',
            $this->factory->getCommitCallback([], $this->resolver, $this->row_builder, [])
        );
    }

    public function testGetIdentifierCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\SelectIdentifierCallback',
            $this->factory->getIdentifierCallback($this->mapper, $this->row_builder, $this->arranger, $this->resolver)
        );
    }

    public function testGetSelectCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\SelectCallback',
            $this->factory->getSelectCallback($this->mapper, $this->row_builder, $this->arranger, $this->resolver)
        );
    }

    public function testGetInsertCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\InsertCallback',
            $this->factory->getInsertCallback());
    }

    public function testGetUpdateCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\UpdateCallback',
            $this->factory->getUpdateCallback());
    }

    public function testGetDeleteCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\DeleteCallback',
            $this->factory->getDeleteCallback());
    }

    public function testNewContext()
    {
        $row = (object)['name' => 'Jon', 'age' => 45];
        $relation_name = '__root';
        $mapper = \Mockery::mock('Aura\SqlMapper_Bundle\RowMapperInterface');

        $context = $this->factory->newContext($row, $relation_name, $mapper);
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\OperationContext',
            $context);
        $this->assertEquals($row, $context->row);
        $this->assertEquals($mapper, $context->mapper);
        $this->assertEquals($relation_name, $context->relation_name);
    }
}
