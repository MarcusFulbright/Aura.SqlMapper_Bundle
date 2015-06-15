<?php
namespace Aura\SqlMapper_Bundle\unit;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;

/**
 * Test OperationCallbackFactoryTest
 * @package Aura\SqlMapper_Bundle\unit
 */
class OperationCallbackFactoryTest extends \PHPUnit_Framework_TestCase 
{
    /** @var OperationCallbackFactory */
    protected $factory;

    public function setUp()
    {
        $this->factory = new OperationCallbackFactory();
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
