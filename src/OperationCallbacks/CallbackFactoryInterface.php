<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\RowCacheInterface;
use Aura\SqlMapper_Bundle\Transaction;

interface CallbackFactoryInterface
{
    /**
     * @param array $operation_list
     * @param PlaceholderResolver $resolver
     * @param MapperLocator $locator
     * @param array $extracted
     * @return CommitCallback
     */
    public function getCommitCallback(
        array $operation_list,
        PlaceholderResolver $resolver,
        MapperLocator $locator,
        array $extracted
    );

    /** @return Transaction */
    public function getTransaction();

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