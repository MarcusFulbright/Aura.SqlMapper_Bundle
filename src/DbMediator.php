<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Class that knows how to coordinate all of the desired DB operations.
 *
 * In charge of being the mediator between the aggregate domain layer and the DB layer. Any time a db operation is
 * required for an aggregate it should go through here. Knows how to use Unit of Work, Transaction (insert, update,
 * delete) and how to handle Select statements.
 */
class DbMediator implements DbMediatorInterface
{
    /** @var MapperLocator */
    protected $locator;

    /** @var Transaction */
    protected $transaction;

    /** @var  OperationArranger */
    protected $operation_arranger;

    /** @var PlaceholderResolver */
    protected $placeholder_resolver;

    /**
     *
     * @param MapperLocator $locator
     * @param Transaction $transaction
     * @param OperationArranger $operation_arranger
     * @param PlaceholderResolver $placeholder_resolver
     *
     */
    public function __construct(
        MapperLocator $locator,
        Transaction $transaction,
        OperationArranger $operation_arranger,
        PlaceholderResolver $placeholder_resolver
    ) {
        $this->locator              = $locator;
        $this->transaction          = $transaction;
        $this->operation_arranger   = $operation_arranger;
        $this->placeholder_resolver = $placeholder_resolver;
    }

    /**
     *
     * Creates, organizes, and executes all of the select queries for the mappers touched
     * by this AggregateMapper based on the provided criteria.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $criteria The array of criteria the object needs to meet.
     *
     * @return array An array representing the db output, as described by row domains.
     *
     */
    public function select(AggregateMapperInterface $mapper, array $criteria = null)
    {
        $results = array();
        $operations = $this->operation_arranger->arrangeForSelect($mapper, $criteria);
        foreach ($operations as $property => $operation) {
            $row_mapper = $this->locator->__get($operation['mapper']);
            if (isset($operation['criteria'])) {
                $field = key($operation['criteria']);
                $value = $this->placeholder_resolver->resolve(current($operation['criteria']), $results, $mapper);
                $results[$property] = $row_mapper->fetchCollectionBy($field, $value);
            } else {
                $results[$property] = $row_mapper->fetchCollection(
                    $row_mapper->select()
                );
            }
        }
        return $results;
    }

    /**
     *
     * Creates a new representation of the provided object in the DB.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $object The instance of the object we want to create.
     *
     * @return bool Whether or not this operation was successful.
     *
     */
    public function create(AggregateMapperInterface $mapper, $object)
    {

    }

    /**
     *
     * Updates the provided object in the DB.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $object The instance of the object we want to update.
     *
     * @return bool Whether or not this operation was successful.
     *
     */
    public function update(AggregateMapperInterface $mapper, $object)
    {

    }

    /**
     *
     * Deletes the provided object from the DB.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $object The instance of the object we want to delete.
     *
     * @return bool Whether or not this operation was successful.
     *
     */
    public function delete(AggregateMapperInterface $mapper, $object)
    {

    }
}