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
 * required for an aggregate it should go through here. Db mediator knows how to craft Select Statements and to use
 * transactions for inserts, updates, and deletes.
 */
class DbMediator implements DbMediatorInterface
{
    /** @var RowObjectBuilder */
    protected $row_builder;

     /** @var  OperationArranger */
    protected $operation_arranger;

    /** @var PlaceholderResolver */
    protected $placeholder_resolver;

    /** @var RowDataExtractor */
    protected $extractor;

    /** @var OperationCallbackFactory */
    protected $callback_factory;

    /**
     * @param RowObjectBuilder $row_builder
     * @param OperationArranger $operation_arranger
     * @param PlaceholderResolver $placeholder_resolver
     * @param RowDataExtractor $extractor
     * @param CallbackFactoryInterface $callback_factory
     */
    public function __construct(
        RowOBjectBuilder $row_builder,
        OperationArranger $operation_arranger,
        PlaceholderResolver $placeholder_resolver,
        RowDataExtractor $extractor,
        CallbackFactoryInterface $callback_factory
    ) {
        $this->row_builder          = $row_builder;
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
     * actual row data objects. Root objects get selected based on the previously obtained primary key and leaf objects
     * get selected based on foreign key relationships.
     *
     * @todo Support multiple criteria
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
            $mapper,
            $this->row_builder,
            $this->operation_arranger,
            $this->placeholder_resolver
        );
        $ids = $select_identifier($path_to_root);
        $mapper_name = $mapper->getRelationToMapper()['__root']['mapper'];
        $primary_field = $this->row_builder->getMapper($mapper_name)->getIdentityField();
        $criteria = ['__root.'.$primary_field => array_column($ids['__root'], $primary_field)];
        $path_from_root = $this->operation_arranger->getPathFromRoot($mapper, $criteria);
        $select = $this->callback_factory->getSelectCallback(
            $mapper,
            $this->row_builder,
            $this->operation_arranger,
            $this->placeholder_resolver
        );
        $results = $select($path_from_root);
        return $results;
    }

    /**
     *
     * Creates new data entries for the given object.
     *
     * For roots, inserts will always get performed, for leaves, inserts and updates will get performed appropriately.
     * Auto-incrementing primary keys will get updated on the given $obj by reference.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $obj The instance of the object we want to create.
     *
     * @return object The given object.
     *
     * @throws Exception\DbOperationException When the transaction fails
     *
     */
    public function create(AggregateMapperInterface $mapper, $obj)
    {
        $this->setPersistOrder($mapper, $obj);
        $extracted = $this->extractor->getRowData($obj, $mapper);
        $operation_list = $this->getOperationList($mapper, $extracted, $this->callback_factory->getInsertCallback());
        $transaction = $this->callback_factory->getTransaction();
        $commit_callback = $this->callback_factory->getCommitCallback(
            $operation_list,
            $this->placeholder_resolver,
            $this->row_builder,
            $extracted
        );
        $this->invokeTransaction($transaction, $commit_callback, $this->getRowMapperLocator($mapper));
        $this->updatePrimaryProperty($extracted, $mapper->getRelationToMapper());
        return $obj;
    }

    /**
     *
     * Updates the provided object in the DB.
     *
     * For roots, update will always get performed, for leaves updates and inserts will get performed accordingly.
     * Auto-incrementing primary keys will get updated on the given obj by reference.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $obj The instance of the object we want to update.
     *
     * @throws DbOperationException When the transaction fails
     *
     * @return object
     *
     */
    public function update(AggregateMapperInterface $mapper, $obj)
    {
        $this->setPersistOrder($mapper, $obj);
        $extracted = $this->extractor->getRowData($obj, $mapper);
        $operation_list = $this->getOperationList($mapper, $extracted, $this->callback_factory->getUpdateCallback());
        $transaction = $this->callback_factory->getTransaction();
        $commit_callback = $this->callback_factory->getCommitCallback(
            $operation_list,
            $this->placeholder_resolver,
            $this->row_builder,
            $extracted
        );
        $this->invokeTransaction($transaction, $commit_callback, $this->getRowMapperLocator($mapper));
        $this->updatePrimaryProperty($extracted, $mapper->getRelationToMapper());
        return $obj;
    }

    /**
     *
     * Deletes the provided object from the DB.
     *
     * For now, everything on the root will get deleted from the DB. Destroy relationships by setting properties to null
     * before calling delete to avoid deleting them form the DB.
     *
     * @todo add a way to configure delete behavior.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $object The instance of the object we want to delete.
     *
     * @return object Whether or not this operation was successful.
     *
     * @throws DbOperationException When the Transaction fails
     *
     */
    public function delete(AggregateMapperInterface $mapper, $object)
    {
        $this->setPersistOrder($mapper, $object);
        $extracted = $this->extractor->getRowData($object, $mapper);
        $operation_list = $this->getOperationList($mapper, $extracted, $this->callback_factory->getDeleteCallback());
        $transaction = $this->callback_factory->getTransaction();
        $commit_callback = $this->callback_factory->getCommitCallback(
            $operation_list,
            $this->placeholder_resolver,
            $this->row_builder,
            $extracted
        );
        $this->invokeTransaction($transaction, $commit_callback, $this->getRowMapperLocator($mapper));
        return true;
    }

    /**
     *
     * Invokes a transaction with the given callback in a try-catch.
     *
     * @param Transaction $transaction
     *
     * @param CommitCallback $call_back
     *
     * @param RowMapperLocator $locator
     *
     * @throws DbOperationException When the transaction fails.
     *
     */
    protected function invokeTransaction(Transaction $transaction, CommitCallback $call_back, RowMapperLocator $locator)
    {
        try {
            $transaction->__invoke($call_back, $locator);
        } catch (\Exception $e) {
            throw new DbOperationException($e->getMessage());
        }
    }

    /**
     *
     * Goes through each row in each relation, passing a context object into the given callable to configure the
     * OperationContext object.
     *
     * @param AggregateMapperInterface $mapper
     *
     * @param array $extracted_rows Row data from the RowDataExtractor
     *
     * @param callable $func function with logic to apply to each row that generates the appropriate OperationContext
     *
     * @return array Returns an array of OperationContext objects that contain all data needed to craft a SQl query
     *
     */
    protected function getOperationList(AggregateMapperInterface $mapper, $extracted_rows, Callable $func)
    {
        $operation_list = [];
        $relation_mapper = $mapper->getRelationToMapper();
        foreach ($extracted_rows as $relation_name => $rows) {
            $mapper_name = $relation_mapper[$relation_name]['mapper'];
            $row_mapper = $this->row_builder->getMapper($mapper_name);
            foreach ($rows as $row) {
                $data = isset($row->row_data) ? $row->row_data : $row;
                $operation_list[] = $func(
                    $this->callback_factory->newContext($data, $relation_name, $row_mapper)
                );
            }
        }
        return $operation_list;
    }

    /**
     *
     * Handles setting the persist order on the given aggregate mapper
     *
     * @param AggregateMapperInterface $mapper
     *
     * @param $object
     *
     * @return void
     */
    protected function setPersistOrder(AggregateMapperInterface $mapper, $object)
    {
        if ($mapper->getPersistOrder() === null) {
            $root_mapper = $this->row_builder->getMapper($mapper->getRelationToMapper()['__root']['mapper']);
            $primary_key = $root_mapper->getIdentityField();
            $primary_value = $root_mapper->getIdentityValue($object);
            $criteria = array($primary_key => $primary_value);
            $order = $this->operation_arranger->getPathFromRoot($mapper, $criteria);
            $mapper->setPersistOrder($order);
        }
    }

    /**
     *
     * Handles updating primary keys on domain objects based on the values in row data objects.
     *
     * @param array $relation_list Arrays of row data indexed by relation name
     *
     * @param array $relation_mapper Output from AggregateMapper->getRelationToMapper();
     *
     */
    protected function updatePrimaryProperty(array $relation_list, array $relation_mapper)
    {
        foreach ($relation_list as $relation_name => $rows) {
            $mapper_name = $relation_mapper[$relation_name]['mapper'];
            $row_mapper = $this->row_builder->getMapper($mapper_name);
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

    /**
     *
     * Gets a RowMapperLocator for the given Aggregate mapper
     *
     * @param AggregateMapperInterface $mapper
     *
     * @return RowMapperLocator
     *
     */
    protected function getRowMapperLocator(AggregateMapperInterface $mapper)
    {
        return $this->row_builder->getLocatorForMappers(
            $mapper->getMapperNames()
        );
    }
}