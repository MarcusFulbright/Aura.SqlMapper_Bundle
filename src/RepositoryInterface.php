<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Interface BuilderInterface
 * @package Aura\SqlMapper_Bundle
 */
interface RepositoryInterface
{
    /**
     *
     * Returns a collection of the specified object, each member of which matches the provided criteria.
     *
     * @param  string $mapper_name The key of the mapper.
     *
     * @param  array  $criteria An array of criteria, describing the objects to be returned.
     *
     * @return mixed An instance of the object's collection, as defined by its factory
     *
     */
    public function fetchCollection($mapper_name, array $criteria = []);

    /**
     *
     * Returns a single instance of the specified object that matches the provided criteria.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param array $criteria An array of criteria, describing the object to be returned.
     *
     * @return mixed An instance of the object
     *
     */
    public function fetchObject($mapper_name, array $criteria = []);

    /**
     *
     * Executes a select based on the given criteria for the given mapper based on the given mapper_name.
     *
     * @param string $mapper_name The key of the mapper.
     *
     * @param array $criteria An array of criteria, describing (from the object's perspective) the data to return.
     *
     * @return array An arranged array of arranged DB output.
     *
     */
    public function select($mapper_name, array $criteria = []);

    /**
     *
     * Executes an update for the provided object.
     *
     * @param string $mapper_name The key of the mapper.
     *
     * @param mixed $object The object instance to update.
     *
     * @return bool Whether or not the update was successful.
     *
     */
    public function update($mapper_name, $object);

    /**
     *
     * Executes an save for the provided object.
     *
     * @param string $mapper_name The key of the mapper.
     *
     * @param mixed $object The object instance to save.
     *
     * @return bool Whether or not the create was successful.
     *
     */
    public function create($mapper_name, $object);

    /**
     *
     * Executes a delete for the provided object.
     *
     * @param string $mapper_name The key of the mapper.
     *
     * @param mixed $object The object instance to delete.
     *
     * @return bool Whether or not the delete was successful.
     *
     */
    public function delete($mapper_name, $object);

    /**
     *
     * Returns the mapper for the given mapper_name
     *
     * @param string $mapper_name
     *
     * @return mixed Either a RowMapperInterface or AggregateMapperInterface
     *
     */
    public function getMapper($mapper_name);
}