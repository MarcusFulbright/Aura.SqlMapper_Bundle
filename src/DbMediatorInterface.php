<?php
/**
 * Created by IntelliJ IDEA.
 * User: conlanc
 * Date: 5/12/2015
 * Time: 3:53 PM
 */

namespace Aura\SqlMapper_Bundle;

interface DbMediatorInterface
{
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
    public function select(AggregateMapperInterface $mapper, array $criteria = null);

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
    public function create(AggregateMapperInterface $mapper, $object);

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
     */
    public function update(AggregateMapperInterface $mapper, $object);

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
     */
    public function delete(AggregateMapperInterface $mapper, $object);

}