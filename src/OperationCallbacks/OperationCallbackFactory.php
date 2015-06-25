<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\RowCacheInterface;
use Aura\SqlMapper_Bundle\Transaction;

class OperationCallbackFactory implements CallbackFactoryInterface
{
    /** @return Transaction */
    public function getTransaction()
    {
        return new Transaction();
    }

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
    ) {
        return new CommitCallback($operation_list, $resolver, $locator, $extracted);
    }

    /**
     * @param AggregateMapperInterface $mapper
     * @param MapperLocator $locator
     * @param OperationArranger $arranger
     * @param PlaceholderResolver $resolver
     * @return SelectIdentifierCallback
     */
    public function getIdentifierCallback(
        AggregateMapperInterface $mapper,
        MapperLocator $locator,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        return new SelectIdentifierCallback($mapper, $locator, $arranger, $resolver);
    }

    /**
     * @param MapperLocator $locator
     * @param AggregateMapperInterface $mapper
     * @param OperationArranger $arranger
     * @param PlaceholderResolver $resolver
     * @return SelectCallback
     */
    public function getSelectCallback(
        AggregateMapperInterface $mapper,
        MapperLocator $locator,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        return new SelectCallback($mapper, $locator, $arranger, $resolver);
    }

    /** @return InsertCallback */
    public function getInsertCallback()
    {
        return new InsertCallback();
    }

    /** @return UpdateCallback */
    public function getUpdateCallback()
    {
        return new UpdateCallback();
    }

    /** @return DeleteCallback */
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