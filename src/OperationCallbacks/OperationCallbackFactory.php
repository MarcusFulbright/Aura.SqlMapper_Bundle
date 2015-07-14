<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\EntityMediation\Transaction;
use Aura\SqlMapper_Bundle\Entity\EntityMapperInterface;
use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\EntityMediation\OperationArranger;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver;

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
        array $extracted
    ) {
        return new CommitCallback($operation_list, $resolver, $extracted);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCallback(
        AggregateMapperInterface $mapper,
        EntityRepository $entity_repository,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        return new SelectIdentifierCallback($mapper, $entity_repository, $arranger, $resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCallback(
        AggregateMapperInterface $mapper,
        EntityRepository $entityRepository,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        return new SelectCallback($mapper, $entityRepository, $arranger, $resolver);
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
    public function newContext(\stdClass $row, $relation_name, EntityMapperInterface $mapper)
    {
        return new OperationContext($row, $relation_name, $mapper);
    }
}