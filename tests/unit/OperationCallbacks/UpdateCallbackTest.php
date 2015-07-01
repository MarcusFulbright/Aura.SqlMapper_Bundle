<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\OperationCallbacks\UpdateCallback;
use Mockery\MockInterface;

class UpdateCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var UpdateCallback */
    protected $callback;

    /** @var \stdClass */
    protected $row;

    protected $mapper_name = 'Row_Data_Mapper_Name';

    protected $relation_name = '__root';

    public function setUp()
    {
        $this->row = (object)['name' => 'Jon', 'age' => 45];
        $this->callback = new UpdateCallback();
    }

    public function tearDown()
    {
        \Mockery::close();
    }

        protected function getContext()
    {
        $context = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\OperationContext');
        $context->mapper = \Mockery::mock('Aura\SqlMapper_Bundle\MapperInterface');
        $context->relation_name = $this->relation_name;
        $context->row = $this->row;
        return $context;
    }
    public function testUpdateInCacheRootStillUpdate()
    {
        $context = $this->getContext();
        $context->mapper->shouldReceive('rowExists')->with($this->row)->once()->andReturn(true);
        $result = $this->callback->__invoke($context);
        $this->assertEquals('update', $result->method);
    }

    public function testUpdateCacheNotRootUpdate()
    {
        $context = $this->getContext();
        $context->relation_name = 'notRoot';
        $context->mapper->shouldReceive('rowExists')->with($this->row)->once()->andReturn(true);
        $result = $this->callback->__invoke($context);
        $this->assertEquals('update', $result->method);
    }
}
