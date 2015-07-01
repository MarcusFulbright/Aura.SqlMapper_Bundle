<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\OperationCallbacks\DeleteCallback;
use Mockery\MockInterface;

class DeleteCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var DeleteCallback */
    protected $callback;

    /** @var \stdClass */
    protected $row;

    protected $mapper_name = 'Row_Data_Mapper_Name';

    protected $relation_name = '__root';

    public function setUp()
    {
        $this->row = (object)['name' => 'Jon', 'age' => 45];
        $this->callback = new DeleteCallback();
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

    public function testDeleteInCache()
    {
        $context = $this->getContext();
        $context->mapper->shouldReceive('rowExists')->with($this->row)->once()->andReturn(true);
        $result = $this->callback->__invoke($context);
        $this->assertEquals('delete', $result->method);
    }

    public function testDeleteNotInCache()
    {
        $context = $this->getContext();
        $context->mapper->shouldReceive('rowExists')->with($this->row)->once()->andReturn(false);
        $result = $this->callback->__invoke($context);
        $this->assertEquals(null, $result->method);
    }
}
