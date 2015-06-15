<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\OperationCallbacks\UpdateCallback;
use Mockery\MockInterface;

class UpdateCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var UpdateCallback */
    protected $callback;

    /** @var MockInterface */
    protected $uow;

    /** @var \stdClass */
    protected $row;

    protected $mapper_name = 'Row_Data_Mapper_Name';

    protected $relation_name = '__root';

    public function setUp()
    {
        $this->row = (object)['name' => 'Jon', 'age' => 45];
        $this->uow = \Mockery::mock('Aura\SqlMapper_Bundle\UnitOfWork');
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
        $this->uow->shouldReceive('update')->with($this->mapper_name, $this->row)->once();
        $this->uow->shouldReceive('insert')->withAnyArgs()->never();
        $this->callback->__invoke($this->uow, $context);
    }

    public function testUpdateInCacheRootStillUpdate()
    {
        $context = $this->getContext();
        $context->cache->shouldReceive('isCached')->with($this->row)->once()->andReturn(true);
        $this->uow->shouldReceive('update')->with($this->mapper_name, $this->row)->once();
        $this->uow->shouldReceive('insert')->withAnyArgs()->never();
        $this->callback->__invoke($this->uow, $context);
    }

    public function testUpdateCacheNotRootUpdate()
    {
        $context = $this->getContext();
        $context->relation_name = 'notRoot';
        $context->cache->shouldReceive('isCached')->with($this->row)->once()->andReturn(true);
        $this->uow->shouldReceive('update')->with($this->mapper_name, $this->row)->once();
        $this->uow->shouldReceive('insert')->withAnyArgs()->never();
        $this->callback->__invoke($this->uow, $context);
    }
}
