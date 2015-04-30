<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Query\Select;

/**
 *
 * Interface that clients can rely upon to access the DB.
 *
 */
interface ManagerInterface
{
    /**
     *
     * Returns a new select query from the gateway.
     *
     * @return Select
     */
    public function select();

    /**
     * Returns a new select query from the gateway for the given columns and values.
     *
     * @param $address
     * @param $val
     * @return mixed
     */
    public function selectBy($address, $val);

    /**
     * Creates and Performs an insert statement for the given object.
     *
     * Returns false upon failure, and will return the object with any additional primary keys upon success.
     *
     * @param mixed $object object to create the statement for
     *
     * @return bool|object
     *
     */
    public function insert($object);

    /**
     *
     * Creates and Performs an update statement for the given object.
     *
     * Will parse out sub-objects that exist on properties and correctly perform updates or inserts appropriately.
     *
     * @param mixed $object object to update.
     *
     * @param null $initial_data If present, only the updated properties on $object will get processed
     *
     * @return bool|object
     *
     */
    public function update($object, $initial_data = null);

    /**
     *
     * Creates and Performs a delete statement for the given object.
     *
     * Will create and perform deletes for any sub-objects.
     *
     * @param mixed $object the object to delete
     *
     * @return bool
     *
     */
    public function delete($object);

    /**
     *
     * Performs the given select and attempts to instantiate a single object from the results.
     *
     * @param Select $select
     *
     * @return bool|object
     *
     */
    public function fetchObject(Select $select);

    /**
     *
     * Creates and executes a select statement with the given 'where' criteria and attempts to instantiate a single
     * object from the results.
     *
     * @param $prop
     *
     * @param $val
     *
     * @return bool|object
     *
     */
    public function fetchObjectBy($prop, $val);

    /**
     *
     * Executes the given select statement and creates a collection of objects from the results.
     *
     * @param Select $select
     *
     * @return bool|object
     *
     */
    public function fetchCollection(Select $select);

    /**
     *
     * Creates an Executes a select statement with the given 'where' criteria and creates a collection of objects from
     * the results.
     *
     * @param $prop
     *
     * @param $val
     *
     * @return bool|object
     *
     */
    public function fetchCollectionBy($prop, $val);
}