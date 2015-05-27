<?php
/**
 * Created by IntelliJ IDEA.
 * User: conlanc
 * Date: 5/14/2015
 * Time: 1:25 PM
 */

namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Query\Select;

abstract class AbstractCachingMapper extends AbstractMapper implements CachingMapperInterface
{

    /**
     * @var RowCache
     */
    protected $cache;

    /**
     *
     * Constructor.
     *
     * @param GatewayInterface $gateway A row data gateway.
     *
     * @param ObjectFactoryInterface $object_factory An object factory.
     *
     * @param FilterInterface $filter A filter for inserts and updates.
     *
     */
    public function __construct(
        GatewayInterface $gateway,
        ObjectFactoryInterface $object_factory,
        FilterInterface $filter
    ) {
        parent::__construct($gateway, $object_factory, $filter);
        $this->cache = new RowCache($this->getIdentityField());
    }

    /**
     *
     * Getter for the RowCache on this object.
     *
     * @return RowCache
     *
     */
    public function getRowCache()
    {
        return $this->cache;
    }

    /**
     *
     * Adds a not-in clause to the select query.
     *
     * @param Select $select
     *
     * @param array $ids
     *
     */
    protected function excludeIdsFromSelect(Select $select, $ids)
    {
        return $this->gateway->excludeValues($select, $this->gateway->getPrimaryCol(), $ids);
    }

    /**
     *
     * Returns an individual object from the gateway using a Select.
     *
     * @param Select $select Select statement for the individual object.
     *
     * @param array $exclude_ids If set, will add a not in clause to the query.
     *
     * @return false|object
     */
    public function fetchObject(Select $select, array $exclude_ids = null)
    {
        if ($exclude_ids) {
            $this->excludeIdsFromSelect($select, $exclude_ids);
        }
        $row = parent::fetchObject($select);
        if ($row) {
            $this->cache->set($row);
        }
        return $row;
    }

    /**
     *
     * Returns an individual object from the gateway, for a given column and
     * value.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value to match against; this can be an array
     * of values.
     *
     * @return object|false
     *
     */
    public function fetchObjectBy($col, $val)
    {
        if ($results = $this->cache->queryCache($col, $val)->results) {
            return $results[0];
        }
        return parent::fetchObjectBy($col, $val);
    }

    /**
     *
     * Returns an array of individual objects from the gateway using a Select;
     * the array is keyed on the values of a specified object field.
     *
     * @param Select $select Select statement for the individual objects.
     *
     * @param mixed $field Key the array on the values of this object field.
     *
     * @param array $exclude_ids If set, will add a not in clause to the query.
     *
     * @return array
     */
    public function fetchObjects(Select $select, $field, array $exclude_ids = null)
    {
        if ($exclude_ids) {
            $select = $this->excludeIdsFromSelect($select, $exclude_ids);
        }
        $results = parent::fetchObjects($select, $field);
        foreach ($results as $result){
            $this->cache->set($result);
        }
        return $results;
    }

    /**
     *
     * Returns an array of individual objects from the gateway for a given
     * column and value(s); the array is keyed on the values of a specified
     * object field.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value to match against; this can be an array
     * of values.
     *
     * @param mixed $field Key the array on the values of this object field.
     *
     * @return object|false
     *
     */
    public function fetchObjectsBy($col, $val, $field)
    {
        $cached = $this->cache->queryCache($col, $val);
        $select = $this->selectBy($col, $val);
        $fromDB = $this->fetchObjects($select, $field, $cached->ids);
        foreach ($cached->results as $row) {
            $fromDB[$row->$field] = $row;
        }
        return $fromDB;
    }

    /**
     *
     * Returns a collection from the gateway using a Select.
     *
     * @param Select $select Select statement for the collection.
     *
     * @param array $exclude_ids If set, will add a not in clause to the query.
     *
     * @return object|array
     *
     */
    public function fetchCollection(Select $select, array $exclude_ids = null)
    {
        if ($exclude_ids) {
            $this->excludeIdsFromSelect($select, $exclude_ids);
        }
        $collection = parent::fetchCollection($select);
        foreach ($collection as $row) {
            $this->cache->set($row);
        }
        return $collection;
    }

    /**
     *
     * Returns a collection from the gateway, for a given column and value(s).
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value to match against; this can be an array
     * of values.
     *
     * @return object|array
     *
     */
    public function fetchCollectionBy($col, $val)
    {
        $cached = $this->cache->queryCache($col, $val);
        $select = $this->selectBy($col, $val);
        $fromDB = $this->fetchCollection($select, $cached->ids);
        foreach ($cached->results as $row) {
            $fromDB[] = $row;
        }
        return $fromDB;
    }

    /**
     *
     * Returns an array of collections from the gateway using a Select;
     * the array is keyed on the values of a specified object field.
     *
     * @param Select $select Select statement for the collections.
     *
     * @param mixed $field Key the array on the values of this object field.
     *
     * @param array $exclude_ids If set, will add a not in clause to the query.
     *
     * @return array
     *
     */
    public function fetchCollections(Select $select, $field, array $exclude_ids = null)
    {
        if ($exclude_ids) {
            $this->excludeIdsFromSelect($select, $exclude_ids);
        }
        $collections = parent::fetchCollections($select, $field);
        foreach ($collections as $collection) {
            foreach ($collection as $row) {
                $this->cache->set($row);
            }
        }
        return $collections;
    }

    /**
     *
     * Returns an array of collections from the gateway, for a given column and
     * value(s); the array is keyed on the values of a specified object field.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value to match against; this can be an array
     * of values.
     *
     * @param mixed $field Key the array on the values of this object field.
     *
     * @return object|false
     *
     */
    public function fetchCollectionsBy($col, $val, $field = null)
    {
        $cached = $this->cache->queryCache($col, $val);
        $select = $this->selectBy($col, $val);
        $fromDB = $this->fetchCollections($select, $field, $cached->ids);
        foreach ($cached->results as $row) {
            if (!isset($fromDB[$row->$field])) {
                $fromDB[$row->$field] = $this->newCollection();
            }
            $fromDB[$row->$field][] = $row;
        }
        return $fromDB;
    }

    /**
     *
     * Inserts an individual object through the gateway.
     *
     * @param object $object The individual object to insert.
     *
     * @return bool
     *
     */
    public function insert($object)
    {
        $this->filter->forInsert($object);

        $data = $this->getRowData($object);
        $row = $this->gateway->insert($data);
        if (! $row) {
            return false;
        }

        if ($this->gateway->isAutoPrimary()) {
            $this->setIdentityValue(
                $object,
                $this->gateway->getPrimaryVal($row)
            );
        }

        $this->cache->set($object);

        return true;
    }

    /**
     *
     * Updates an individual object through the gateway; if an array of initial
     * data is present, updates only changed values.
     *
     * @param object $object The individual object to update.
     *
     * @param array $initial_data Initial data for the individual object.
     *
     * @return bool True if the update succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function update($object, $initial_data = null)
    {
        $this->filter->forUpdate($object);
        $cached = $this->cache->getCachedData($object);
        $data = $this->getRowData($object, $cached);

        // No-op and return true if there are no changes.
        if (array_keys($data) == array($this->gateway->getPrimaryCol())) {
            return true;
        }

        if ($results = (bool) $this->gateway->update($data)) {
            $this->cache->set($object);
        }
        return $results;
    }

    /**
     *
     * Deletes an individual object through the gateway.
     *
     * @param object $object The individual object to delete.
     *
     * @return bool True if the delete succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function delete($object)
    {
        $row = $this->getRowData($object);
        if ($results = (bool) $this->gateway->delete($row)) {
            $this->cache->removeCachedVersion($object);
        }
        return $results;
    }

}