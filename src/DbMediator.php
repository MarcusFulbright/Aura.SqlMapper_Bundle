<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Exception\DbOperationException;
use Aura\SqlMapper_Bundle\OperationCallbacks\CallbackFactoryInterface;
use Aura\SqlMapper_Bundle\OperationCallbacks\CommitCallback;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;

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

     /** @var  OperationArranger */
    protected $operation_arranger;

    /** @var PlaceholderResolver */
    protected $placeholder_resolver;

    /** @var RowDataExtractor */
    protected $extractor;

    /** @var OperationCallbackFactory */
    protected $callback_factory;

    /**
     * @param MapperLocator $locator
     * @param OperationArranger $operation_arranger
     * @param PlaceholderResolver $placeholder_resolver
     * @param RowDataExtractor $extractor
     * @param CallbackFactoryInterface $callback_factory
     */
    public function __construct(
        MapperLocator $locator,
        OperationArranger $operation_arranger,
        PlaceholderResolver $placeholder_resolver,
        RowDataExtractor $extractor,
        CallbackFactoryInterface $callback_factory
    ) {
        $this->locator              = $locator;
        $this->operation_arranger   = $operation_arranger;
        $this->placeholder_resolver = $placeholder_resolver;
        $this->extractor            = $extractor;
        $this->callback_factory     = $callback_factory;
    }

    /**
     *
     * Performs a select statement with optional criteria.
     *
     * When criteria is present, this method will select primary keys and join properties up to the root table, not the
     * actual row data objects. After selecting the appropriate info for the root table, all row data objects for the
     * root will get selected based on their primary keys. This method then uses the relation map and the root row data
     * objects to crawl through a dependency chain built from the relation map to select all leaf tables.
     *
     * @todo Support multiple criteria
     * Context factory and move some of this stuff in there?
     *
     * @param AggregateMapperInterface $mapper
     *
     * @param array $criteria
     *
     * @throws Exception\NoSuchMapper
     *
     * @return array
     *
     */
    public function select(AggregateMapperInterface $mapper, array $criteria = [])
    {
        $path_to_root      = $this->operation_arranger->getPathToRoot($mapper, $criteria);
        $select_identifier = $this->callback_factory->getIdentifierCallback(
            $this->locator,
            $this->operation_arranger,
            $mapper,
            $this->placeholder_resolver
        );
        $ids = $select_identifier($path_to_root);
        $primary_field = $this->locator->__get($mapper->getRelationToMapper()['__root']['mapper'])->getIdentityField();
        $criteria = ['__root.'.$primary_field => array_column($ids['__root'], $primary_field)];
        $path_from_root = $this->operation_arranger->getPathFromRoot($mapper, $criteria);
        $select = $this->callback_factory->getSelectCallback(
            $this->locator,
            $mapper,
            $this->operation_arranger,
            $this->placeholder_resolver
        );
        $results = $select($path_from_root);
        return $results;
    }

    /**
     *
     * Creates a new representation of the provided object in the DB.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $obj The instance of the object we want to create.
     *
     * @return bool Whether or not this operation was successful.
     *
     * @throws Exception\DbOperationException
     */
    public function create(AggregateMapperInterface $mapper, $obj)
    {
        if ($mapper->getPersistOrder() === null) {
            $this->setPersistOrder($mapper, $obj);
        }
        $extracted = $this->extractor->getRowData($obj, $mapper);
        $operation_list = $this->getOperationList($mapper, $extracted, $this->callback_factory->getInsertCallback());
        $transaction = $this->callback_factory->getTransaction();
        $commit_callback = $this->callback_factory->getCommitCallback(
            $operation_list,
            $this->placeholder_resolver,
            $this->locator,
            $extracted
        );
        $this->invokeTransaction($transaction, $commit_callback);
        $this->updatePrimaryProperty($extracted, $mapper->getRelationToMapper());
        return $obj;
    }

    /**
     *
     * Updates the provided object in the DB.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $obj The instance of the object we want to update.
     *
     * @throws DbOperationException
     *
     * @return object
     */
    public function update(AggregateMapperInterface $mapper, $obj)
    {
        if ($mapper->getPersistOrder() === null) {
            $this->setPersistOrder($mapper, $obj);
        }
        $extracted = $this->extractor->getRowData($obj, $mapper);
        $operation_list = $this->getOperationList($mapper, $extracted, $this->callback_factory->getUpdateCallback());
        $transaction = $this->callback_factory->getTransaction();
        $commit_callback = $this->callback_factory->getCommitCallback(
            $operation_list,
            $this->placeholder_resolver,
            $this->locator,
            $extracted
        );
        $this->invokeTransaction($transaction, $commit_callback);
        $this->updatePrimaryProperty($extracted, $mapper->getRelationToMapper());
        return $obj;
    }

    /**
     *
     * Deletes the provided object from the DB.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $object The instance of the object we want to delete.
     *
     * @return object Whether or not this operation was successful.
     *
     * @throws DbOperationException
     */
    public function delete(AggregateMapperInterface $mapper, $object)
    {
        if ($mapper->getPersistOrder() === null) {
            $this->setPersistOrder($mapper, $object);
        }
        $extracted = $this->extractor->getRowData($object, $mapper);
        $operation_list = $this->getOperationList($mapper, $extracted, $this->callback_factory->getDeleteCallback());
        $transaction = $this->callback_factory->getTransaction();
        $commit_callback = $this->callback_factory->getCommitCallback(
            $operation_list,
            $this->placeholder_resolver,
            $this->locator,
            $extracted
        );
        $this->invokeTransaction($transaction, $commit_callback);
        return true;
    }

    protected function invokeTransaction(Transaction $transaction, CommitCallback $call_back)
    {
        try {
            $transaction->__invoke($call_back, $this->locator);
        } catch (\Exception $e) {
            throw new DbOperationException($e->getMessage());
        }
    }

    protected function getOperationList(AggregateMapperInterface $mapper, $extracted_rows, Callable $func)
    {
        $operation_list = [];
        $relation_mapper = $mapper->getRelationToMapper();
        foreach ($extracted_rows as $relation_name => $rows) {
            $mapper_name = $relation_mapper[$relation_name]['mapper'];
            $row_mapper = $this->locator->__get($mapper_name);
            $cache = $row_mapper->getRowCache();
            foreach ($rows as $row) {
                $data = isset($row->row_data) ? $row->row_data : $row;
                $operation_list[] = $func(
                    $this->callback_factory->newContext($data, $mapper_name, $relation_name, $cache)
                );
            }
        }
        return $operation_list;
    }

    protected function setPersistOrder(AggregateMapperInterface $mapper, $object)
    {
        $root_mapper = $this->locator->__get($mapper->getRelationToMapper()['__root']['mapper']);
        $primary_key = $root_mapper->getIdentityField();
        $primary_value = $root_mapper->getIdentityValue($object);
        $criteria = array($primary_key => $primary_value);
        $order = $this->operation_arranger->getPathFromRoot($mapper, $criteria);
        $mapper->setPersistOrder($order);
    }

    protected function updatePrimaryProperty($relation_list, $relation_mapper)
    {
        foreach ($relation_list as $relation_name => $rows) {
            $mapper_name = $relation_mapper[$relation_name]['mapper'];
            $row_mapper = $this->locator->__get($mapper_name);
            if ($row_mapper->isAutoPrimary() === true) {
                $id_field = $row_mapper->getIdentityField();
                $id_property = $relation_mapper[$relation_name]['fields'][$id_field];
                foreach ($rows as $row) {
                    $value = $row_mapper->getIdentityValue($row->row_data);
                    $instance = $row->instance;
                    $refl = new \ReflectionObject($instance);
                    $property = $refl->getProperty($id_property);
                    $property->setAccessible(true);
                    $property->setValue($instance, $value);
                }
            }
        }
    }
}