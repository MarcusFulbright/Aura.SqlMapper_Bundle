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
    protected $locator;

    /** @var MockInterface */
    protected $mapper;

    /** @var MockInterface */
    protected $arranger;

    /** @var MockInterface */
    protected $resolver;

    public function setUp()
    {
        $this->factory = new OperationCallbackFactory();

        $this->locator = \Mockery::mock('Aura\SqlMapper_Bundle\MapperLocator');
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
            $this->factory->getCommitCallback([], $this->resolver, $this->locator, [])
        );
    }

    public function testGetIdentifierCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\SelectIdentifierCallback',
            $this->factory->getIdentifierCallback($this->mapper, $this->locator, $this->arranger, $this->resolver)
        );
    }

    public function testGetSelectCallback()
    {
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\SelectCallback',
            $this->factory->getSelectCallback($this->mapper, $this->locator, $this->arranger, $this->resolver)
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
        $mapper_name = 'Row_Data_Mapper_Name';
        $relation_name = '__root';
        $cache = \Mockery::mock('Aura\SqlMapper_Bundle\RowCacheInterface');

        $context = $this->factory->newContext($row, $mapper_name, $relation_name, $cache);
        $this->assertInstanceOf(
            'Aura\SqlMapper_Bundle\OperationCallbacks\OperationContext',
            $context);
        $this->assertEquals($row, $context->row);
        $this->assertEquals($cache, $context->cache);
        $this->assertEquals($mapper_name, $context->mapper_name);
        $this->assertEquals($relation_name, $context->relation_name);
    }
}
