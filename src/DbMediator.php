<?php
namespace Aura\SqlMapper_Bundle;
use Aura\SqlMapper_Bundle\Exception\DbOperationException;

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

    /** @var  UnitOfWork */
    protected $unit_of_work;

    /**
     *
     * @param MapperLocator $locator
     * @param Transaction $transaction
     * @param OperationArranger $operation_arranger
     * @param PlaceholderResolver $placeholder_resolver
     * @param RowDataExtractor $extractor
     */
    public function __construct(
        MapperLocator $locator,
        Transaction $transaction,
        OperationArranger $operation_arranger,
        PlaceholderResolver $placeholder_resolver,
        RowDataExtractor $extractor
    ) {
        $this->locator              = $locator;
        $this->transaction          = $transaction;
        $this->operation_arranger   = $operation_arranger;
        $this->placeholder_resolver = $placeholder_resolver;
        $this->extractor            = $extractor;
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
     * @todo possibly clean up the logic in this method, Feels really bulky right now
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
    public function select(AggregateMapperInterface $mapper, array $criteria = null)
    {
        $rows = array();
        $relation_to_mapper = $mapper->getRelationToMapper();
        $mapper_name = $relation_to_mapper['__root']['mapper'];
        $row_mapper = $this->locator->__get($mapper_name);
        $id_field = $row_mapper->getIdentityField();
        if ($criteria === null) {
            //no criteria just grab all the root row_data objects
            $rows['__root'] = $row_mapper->fetchCollection(
                $row_mapper->select()
            );
            foreach ($rows['__root'] as $row) {
                $where_in[] = $row->$id_field;
            }
        } else {
            //get the dependency chain to get back to root from the criteria starting place
            $path_to_root = $this->operation_arranger->getPathToRoot($mapper, $criteria);
            $ids = array();
            //go through each relationship and select the primary key and any used join properties.
            foreach ($path_to_root as $key => $relation) {
                $mapper_name = $relation_to_mapper[$relation->relation_name]['mapper'];
                $row_mapper  = $this->locator->__get($mapper_name);
                $val         = $this->placeholder_resolver->resolve(current($relation->criteria), $ids, $mapper);
                $fields      = array_merge($relation->fields, array($row_mapper->getIdentityField()));
                $query       = $row_mapper->selectBy(key($relation->criteria), $val, $fields);
                $pdo         = $row_mapper->getWriteConnection();
                $results     = $pdo->fetchAll($query->__toString(), $query->getBindValues());
                $ids[$relation->relation_name] = $results;
            }
            $where_in = array();
            //build values in a where_in clause that can be used to select all of the appropriate entries
            foreach ($ids['__root'] as $row) {
                $where_in[] = $row[$id_field];
            }
            //select all of the root row data objects
            $rows['__root'] = $row_mapper->fetchCollectionBy($id_field, $where_in);
        }
        //get the tree that can be used to build out an aggregate object starting at the root
        $path_from_root = $this->operation_arranger->getPathFromRoot($mapper, array('__root'.'.'.$id_field=>$where_in));
        //loop over each relationship in this path and select row data objects with the appropriate criteria
        foreach ($path_from_root as $relation) {
            $where_in = $this->placeholder_resolver->resolve(current($relation->criteria), $rows, $mapper);
            $field = key($relation->criteria);
            $mapper_name = $relation_to_mapper[$relation->relation_name]['mapper'];
            $row_mapper = $this->locator->__get($mapper_name);
            $rows[$relation->relation_name] = $row_mapper->fetchCollectionBy($field, $where_in);
        }
        return $rows;
    }

    /**
     *
     * Creates a new representation of the provided object in the DB.
     *
     * @todo this is super ugly. It needs some love.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $object The instance of the object we want to create.
     *
     * @return bool Whether or not this operation was successful.
     *
     * @throws Exception\DbOperationException
     */
    public function create(AggregateMapperInterface $mapper, $object)
    {
        //@todo this should get injected somehow
        $uow = new UnitOfWork($this->locator);
        $relation_mapper = $mapper->getRelationToMapper();
        if ($mapper->getPersistOrder() === null) {
            $this->setPersistOrder($mapper, $object);
        }
        //@todo extrapolate this to its own class
        $callback = function (UnitOfWork $uow, $context) {
            $cache = $context->cache;
            $row = $context->row;
            $is_cached = $cache != null && $cache->isCached($row);
            $is_root = $context->relation_name === '__root';
                if ($is_cached && ! $is_root){
                    $uow->update($context->mapper_name, $row);
                } else {
                    $uow->insert($context->mapper_name, $row);
                }
        };
        $relation_list = $this->populateUnitOfWork($mapper, $object, $uow, $callback);
        if ($uow->exec() !== false ) {
            $this->updatePrimaryProperty($relation_list, $relation_mapper);
            return $object;
        } else {
            $error = $uow->getException();
            $this->unit_of_work = null;
            throw new DbOperationException($error->getMessage());
        }
    }

    protected function populateUnitOfWork(AggregateMapperInterface $mapper, $object, UnitOfWork $uow, Callable $func)
    {
        $relation_list = $this->extractor->getRowData($object, $mapper);
        $relation_mapper = $mapper->getRelationToMapper();
        foreach ($relation_list as $relation_name => $rows) {
            $mapper_name = $relation_mapper[$relation_name]['mapper'];
            $row_mapper = $this->locator->__get($mapper_name);
            $cache = $row_mapper->getRowCache();
            foreach ($rows as $row) {
                /* @todo this should be done in RowDataExtractor */
                $row->row_data = (object)$row->row_data;
                $row_data = $row->row_data;
                $context = (object)[
                    'row' => $row_data,
                    'relation_name' => $relation_name,
                    'cache' => $cache,
                    'mapper_name' => $mapper_name
                ];
                $func($uow, $context);
            }
        }
        return $relation_list;
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
        $this->unit_of_work = null;
    }

    /**
     *
     * Updates the provided object in the DB.
     *
     * @param AggregateMapperInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $object The instance of the object we want to update.
     *
     * @throws DbOperationException
     *
     * @return object
     */
    public function update(AggregateMapperInterface $mapper, $object)
    {
        //@todo this should get injected somehow
        $uow = new UnitOfWork($this->locator);
        $relation_mapper = $mapper->getRelationToMapper();
        if ($mapper->getPersistOrder() === null) {
            $this->setPersistOrder($mapper, $object);
        }
        //@todo extrapolate this to its own class
        $callback = function (UnitOfWork $uow, $context) {
            $cache = $context->cache;
            $row = $context->row;
            $is_cached = $cache != null && $cache->isCached($row);
            $is_root = $context->relation_name === '__root';
                if ($is_cached || $is_root){
                    $uow->update($context->mapper_name, $row);
                } else {
                    $uow->insert($context->mapper_name, $row);
                }
        };
        $relation_list = $this->populateUnitOfWork($mapper, $object, $uow, $callback);
        if ($uow->exec() !== false ) {
            $this->updatePrimaryProperty($relation_list, $relation_mapper);
            return $object;
        } else {
            $error = $uow->getException();
            $this->unit_of_work = null;
            throw new DbOperationException($error->getMessage());
        }
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
        //@todo this should get injected somehow
        $uow = new UnitOfWork($this->locator);
        $relation_mapper = $mapper->getRelationToMapper();
        if ($mapper->getPersistOrder() === null) {
            $this->setPersistOrder($mapper, $object);
        }
        //@todo extrapolate this to its own class
        $callback = function (UnitOfWork $uow, $context) {
            $cache = $context->cache;
            $row = $context->row;
            if ($cache != null && $cache->isCached($row) === true) {
                return;
            } else {
                $uow->delete($context->mapper_name, $row);
            }
        };
        $relation_list = $this->populateUnitOfWork($mapper, $object, $uow, $callback);
        if ($uow->exec() !== false ) {
            $this->updatePrimaryProperty($relation_list, $relation_mapper);
            return $object;
        } else {
            $error = $uow->getException();
            $this->unit_of_work = null;
            throw new DbOperationException($error->getMessage());
        }
    }
}