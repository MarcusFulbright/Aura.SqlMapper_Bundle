<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\OperationCallbacks\DeleteCallback;
use Mockery\MockInterface;

class DeleteCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var DeleteCallback */
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
        $this->callback = new DeleteCallback();
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

    public function testDeleteNoCache()
    {
        $context = $this->getContext(false);
        $this->uow->shouldReceive('delete')->with($this->mapper_name, $this->row)->once();
        $this->callback->__invoke($this->uow, $context);
    }

    public function testDeleteInCache()
    {
        $context = $this->getContext();
        $context->cache->shouldReceive('isCached')->with($this->row)->once()->andReturn(true);
        $this->uow->shouldReceive('delete')->with($this->mapper_name, $this->row)->once();
        $this->callback->__invoke($this->uow, $context);
    }

    public function testDeleteNotInCache()
    {
        $context = $this->getContext();
        $context->cache->shouldReceive('isCached')->with($this->row)->once()->andReturn(false);
        $this->uow->shouldReceive('delete')->withAnyArgs()->never();
        $this->callback->__invoke($this->uow, $context);
    }
}
