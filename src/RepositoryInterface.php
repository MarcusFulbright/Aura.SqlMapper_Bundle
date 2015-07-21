<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Interface BuilderInterface
 * @package Aura\Sqlbuilder_Bundle
 */
interface RepositoryInterface
{
    /**
     *
     * Returns a collection of the specified object, each member of which matches the provided criteria.
     *
     * @param  string $builder_name The key of the builder.
     *
     * @param  array  $criteria An array of criteria, describing the objects to be returned.
     *
     * @return mixed An instance of the object's collection, as defined by its factory
     *
     */
    public function fetchCollection($builder_name, array $criteria = []);

    /**
     *
     * Returns a single instance of the specified object that matches the provided criteria.
     *
     * @param string $builder_name The key of the row_builder.
     *
     * @param array $criteria An array of criteria, describing the object to be returned.
     *
     * @return mixed An instance of the object
     *
     */
    public function fetchObject($builder_name, array $criteria = []);

    /**
     *
     * Executes a select based on the given criteria for the given builder based on the given builder_name.
     *
     * @param string $builder_name The key of the builder.
     *
     * @param array $criteria An array of criteria, describing (from the object's perspective) the data to return.
     *
     * @return array An arranged array of arranged DB output.
     *
     */
    public function select($builder_name, array $criteria = []);

    /**
     *
     * Executes an update for the provided object.
     *
     * @param string $builder_name The key of the builder.
     *
     * @param mixed $object The object instance to update.
     *
     * @return bool Whether or not the update was successful.
     *
     */
    public function update($builder_name, $object);

    /**
     *
     * Executes an save for the provided object.
     *
     * @param string $builder_name The key of the builder.
     *
     * @param mixed $object The object instance to save.
     *
     * @return bool Whether or not the create was successful.
     *
     */
    public function create($builder_name, $object);

    /**
     *
     * Executes a delete for the provided object.
     *
     * @param string $builder_name The key of the builder.
     *
     * @param mixed $object The object instance to delete.
     *
     * @return bool Whether or not the delete was successful.
     *
     */
    public function delete($builder_name, $object);
}