<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Interface CompoundGatewayInterface
 * @package Aura\SqlMapper_Bundle
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
     * @return Select
     *
     */
    public function selectBy($col, $val, array $cols = []);

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
}