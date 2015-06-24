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

        protected function getContext($cache = true)
    {
        $context = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\OperationContext');
        $context->cache = $cache == true ? \Mockery::mock('Aura\SqlMapper_Bundle\RowCacheInterface') : null;
        $context->mapper_name = $this->mapper_name;
        $context->relation_name = $this->relation_name;
        $context->row = $this->row;
        return $context;
    }

    public function testUpdateNoCache()
    {
        $context = $this->getContext(false);
        $result = $this->callback->__invoke($context);
        $this->assertEquals('update', $result->method);
    }

    public function testUpdateInCacheRootStillUpdate()
    {
        $context = $this->getContext();
        $context->cache->shouldReceive('isCached')->with($this->row)->once()->andReturn(true);
        $result = $this->callback->__invoke($context);
        $this->assertEquals('update', $result->method);
    }

    public function testUpdateCacheNotRootUpdate()
    {
        $context = $this->getContext();
        $context->relation_name = 'notRoot';
        $context->cache->shouldReceive('isCached')->with($this->row)->once()->andReturn(true);
        $result = $this->callback->__invoke($context);
        $this->assertEquals('update', $result->method);
    }
}
