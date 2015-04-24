<?php

namespace Aura\SqlMapper_Bundle;
use Aura\SqlMapper_Bundle\Query\Select;

/**
 *
 * Interface for a compound gateway.
 *
 * @package Aura\SqlMapper_Bundle
 *
 */
interface CompoundGatewayInterface 
{
    /**
     *
     * Returns the database read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection();

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection();

    /**
     *
     * Creates a Select query to match against a given column and value(s).
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     *
     * @param array $cols
     *
     * @return Select
     */
    public function selectBy($col, $val, array $cols = array());

    /**
     *
     * Returns a new Select query for the gateway table using a read
     * connection.
     *
     * @param array $cols Select these columns from the table; when empty,
     * selects all gateway columns.
     *
     * @return Select
     *
     */
    public function select(array $cols);

    /**
     *
     * Inserts a row array into the gateway table using a write connection.
     *
     * @param array $row The row array to insert.
     *
     * @return mixed
     *
     */
    public function insert(array $row);

    /**
     *
     * Updates a row in the table using a write connection.
     *
     * @param array $row The row array to update.
     *
     * @return bool True if the update succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function update(array $row);

    /**
     *
     * Deletes a row array from the gateway table using a write connection.
     *
     * @param array $row The row array to delete.
     *
     * @return bool True if the delete succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function delete(array $row);


    /**
     *
     * Returns an array of join information indexed by a friendly name.
     *
     * @return array
     */
    public function getJoins();

    /**
     *
     * Executes a select statement and returns the first result.
     *
     * @param Select $select statement to execute
     *
     * @return array
     *
     */
    public function fetchRow(Select $select);

    /**
     *
     * Executes a select statement with the given $vals in the where clause and returns
     * the first result.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     *
     * @param array $cols Select these columns from the table; when empty,
     * selects all gateway columns.
     *
     * @return mixed
     */
    public function fetchRowBy($col, $val, array $cols = array());

    /**
     *
     * Executes a select statement and returns all results.
     *
     * @param Select $select Statement to execute.
     *
     * @return mixed
     */
    public function fetchRows(Select $select);

    /**
     *
     * Executes a select statement with the given $vals in the where clause and returns
     * all results.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     *
     * @param array $cols Select these columns from the table; when empty,
     * selects all gateway columns.
     *
     * @return mixed
     */
    public function fetchRowsBy($col, $val, array $cols = array());
}