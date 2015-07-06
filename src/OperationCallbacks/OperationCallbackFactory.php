<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\RowMapperInterface;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\RowObjectBuilder;
use Aura\SqlMapper_Bundle\Transaction;

class OperationCallbackFactory implements CallbackFactoryInterface
{
    /** @return Transaction */
    public function getTransaction()
    {
        return new Transaction();
    }

    /**
     * {@inheritdoc}
     */
    public function getCommitCallback(
        array $operation_list,
        PlaceholderResolver $resolver,
        RowObjectBuilder $row_builder,
        array $extracted
    ) {
        return new CommitCallback($operation_list, $resolver, $extracted);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCallback(
        AggregateMapperInterface $mapper,
        RowObjectBuilder $row_builder,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        return new SelectIdentifierCallback($mapper, $row_builder, $arranger, $resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCallback(
        AggregateMapperInterface $mapper,
        RowObjectBuilder $row_builder,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        return new SelectCallback($mapper, $row_builder, $arranger, $resolver);
    }

    /** {@inheritdoc} */
    public function getInsertCallback()
    {
        return new InsertCallback();
    }

    /** {@inheritdoc} */
    public function getUpdateCallback()
    {
        return new UpdateCallback();
    }

    /** {@inheritdoc} */
    public function getDeleteCallback()
    {
        return new DeleteCallback();
    }

    /**
     * {@inheritdoc}
     */
    public function newContext(\stdClass $row, $relation_name, RowMapperInterface $mapper)
    {
        return new OperationContext($row, $relation_name, $mapper);
    }
}