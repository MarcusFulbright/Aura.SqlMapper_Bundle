<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\RowCacheInterface;

interface CallbackFactoryInterface
{
    /** @return OperationCallbackInterface */
    public function getInsertCallback();

    /** @return OperationCallbackInterface */
    public function getUpdateCallback();

    /** @return OperationCallbackInterface */
    public function getDeleteCallback();

    /**
     * @param RowCacheInterface $cache
     * @param \stdClass $row
     * @param $mapper_name
     * @param $relation_name
     * @return OperationContext
     */
    public function newContext(\stdClass $row, $mapper_name, $relation_name, RowCacheInterface $cache = null);
}