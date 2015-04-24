<?php

namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Query\Select;

/**
 *
 * Interface CompoundMapperInterface
 *
 * @package Aura\SqlMapper_Bundle
 *
 */
interface CompoundMapperInterface 
{
    /**
     *
     * Returns an individual object from the Select results.
     *
     * @param Select $select Select statement for the individual object.
     *
     * @return mixed
     *
     */
    public function fetchObject(Select $select);

    /**
     *
     * Instantiates a new individual object from an array of row data.
     *
     * @param array $data Row data for the individual object.
     *
     * @return mixed
     *
     */
    public function newObject(array $row = array());

    /**
     *
     * Returns an individual object from the mapped gateway for a given column
     * and value(s).
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     *
     * @return array
     *
     */
    public function fetchObjectBy($col, $val);

    /**
     *
     * Returns a collection from the Select results.
     *
     * @param Select $select Select statement for the collection.
     *
     * @return mixed
     *
     */
    public function fetchCollection(Select $select);

    /**
     *
     * Instantiates a new collection from an array of row data arrays.
     *
     * @param array $rows An array of row data arrays.
     *
     * @return mixed
     *
     */
    public function newCollection(array $rows = array());

    /**
     *
     * Returns a collection from the mapped table for a given column and value.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     *
     * @return array
     *
     */
    public function fetchCollectionBy($col, $val);

    /**
     *
     * Creates a Select query to match against a given column and value(s).
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     *
     * @return Select
     *
     */
    public function selectBy($col, $val);

    /**
     *
     * Returns a new Select query for the mapped gateway using a read
     * connection.
     *
     * @return Select
     *
     */
    public function select();

    /**
     *
     * Inserts an individual object into the mapped table using a write
     * connection.
     *
     * @param object $object The individual object to insert.
     *
     * @return int The number of affected rows.
     *
     */
    public function insert($object);

    /**
     *
     * Updates an individual object in the mapped table using a write
     * connection; if an array of initial data is present, updates only changed
     * values.
     *
     * @param object $object The individual object to update.
     *
     * @param array $initial_data Initial data for the individual object.
     *
     * @return bool True if the update succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function update($object, $initial_data = null);

    /**
     *
     * Deletes an individual object from the mapped table using a write
     * connection.
     *
     * @param object $object The individual object to delete.
     *
     * @return bool True if the delete succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function delete($object);
}