<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\DbMediation\Transaction;
use Aura\SqlMapper_Bundle\Entity\EntityMapperInterface;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver;

/**
 * Defines all the methods used to create Operation Callback methods.
 */
interface CallbackFactoryInterface
{
    /**
     *
     * @param array $operation_list Array of OperationContext objects in the correct order for execution
     *
     * @param PlaceholderResolver $resolver
     *
     * @param array $extracted Row Data extracted from the aggregate object using the RowDataExtractor
     *
     * @return CommitCallback
     *
     */
    public function getCommitCallback(
        array $operation_list,
        PlaceholderResolver $resolver,
        array $extracted
    );

    /** @return Transaction */
    public function getTransaction();

    /** @return InsertCallback */
    public function getInsertCallback();

    /** @return UpdateCallback */
    public function getUpdateCallback();

    /** @return DeleteCallback */
    public function getDeleteCallback();

    /**
     *
     * @param EntityMapperInterface $mapper The appropriate row data mapper
     *
     * @param \stdClass $row Object that represents the appropriate row data, can be obtained form Row Data Extractor
     *
     * @param string $relation_name name of the relation that this row data belongs to according to aggregate map
     *
     * @return OperationContext
     *
     */
    public function newContext(\stdClass $row, $relation_name, EntityMapperInterface $mapper);
}