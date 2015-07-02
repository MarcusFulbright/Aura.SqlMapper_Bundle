<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Interface BuilderInterface
 * @package Aura\SqlMapper_Bundle
 */
interface BuilderInterface 
{
    /**
     *
     * Returns a collection of the specified aggregate, each member of
     * which matches the provided criteria.
     *
     * @param  string $mapper_name The key of the row_mapper.
     *
     * @param  array  $criteria An array of criteria, describing the objects
     * to be returned.
     *
     * @return mixed An instance of the aggregate collection, as defined by
     * the AggregateMapper
     *
     */
    public function fetchCollection($mapper_name, array $criteria = array());

    /**
     *
     * Returns a single instance of the specified aggregate that matches
     * the provided criteria.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param array $criteria An array of criteria, describing the object
     * to be returned.
     *
     * @return mixed An instance of the row, as defined by the
     * RowMapper
     *
     */
    public function fetchObject($mapper_name, array $criteria = array());

    /**
     *
     * Executes a select for all of the mappers in the indicated
     * row_mapper.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param array $criteria An array of criteria, describing (from the
     * object's perspective) the data to return.
     *
     * @return array An arranged array of arranged DB output.
     *
     */
    public function select($mapper_name, array $criteria = array());

    /**
     *
     * Executes an update for the provided object.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param mixed $object The aggregate instance to update.
     *
     * @return bool Whether or not the update was successful.
     *
     */
    public function update($mapper_name, $object);

    /**
     *
     * Executes an save for the provided object.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param mixed $object The aggregate instance to save.
     *
     * @return bool Whether or not the create was successful.
     *
     */
    public function create($mapper_name, $object);

    /**
     *
     * Executes a delete for the provided object.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param mixed $object The aggregate instance to delete.
     *
     * @return bool Whether or not the delete was successful.
     *
     */
    public function delete($mapper_name, $object);
}