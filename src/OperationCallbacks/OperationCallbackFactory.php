<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\RowCacheInterface;

class OperationCallbackFactory implements CallbackFactoryInterface
{
    /** @return OperationCallbackInterface */
    public function getInsertCallback()
    {
        return new InsertCallback();
    }

    /** @return OperationCallbackInterface */
    public function getUpdateCallback()
    {
        return new UpdateCallback();
    }

    /** @return OperationCallbackInterface */
    public function getDeleteCallback()
    {
        return new DeleteCallback();
    }

    /**
     * @param RowCacheInterface $cache
     * @param \stdClass $row
     * @param $mapper_name
     * @param $relation_name
     * @return OperationContext
     */
    public function newContext(\stdClass $row, $mapper_name, $relation_name, RowCacheInterface $cache = null)
    {
        return new OperationContext($row, $mapper_name, $relation_name, $cache);
    }
}