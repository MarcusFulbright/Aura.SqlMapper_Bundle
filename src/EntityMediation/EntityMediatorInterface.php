<?php

namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;

interface EntityMediatorInterface
{
    /**
     *
     * Creates, organizes, and executes all of the select queries for the mappers touched
     * by this AggregateMapper based on the provided criteria.
     *
     * @param AggregateBuilderInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $criteria The array of criteria the object needs to meet.
     *
     * @return array An array representing the db output, as described by row domains.
     *
     */
    public function select(AggregateBuilderInterface $mapper, array $criteria = null);

    /**
     *
     * Creates a new representation of the provided object in the DB.
     *
     * @param AggregateBuilderInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $object The instance of the object we want to create.
     *
     * @return bool Whether or not this operation was successful.
     *
     */
    public function create(AggregateBuilderInterface $mapper, $object);

    /**
     *
     * Updates the provided object in the DB.
     *
     * @param AggregateBuilderInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $object The instance of the object we want to update.
     *
     * @return bool Whether or not this operation was successful.
     */
    public function update(AggregateBuilderInterface $mapper, $object);

    /**
     *
     * Deletes the provided object from the DB.
     *
     * @param AggregateBuilderInterface $mapper The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param array $object The instance of the object we want to delete.
     *
     * @return bool Whether or not this operation was successful.
     */
    public function delete(AggregateBuilderInterface $mapper, $object);

}