<?php
namespace Aura\SqlMapper_Bundle;


class AggregateBuilder
{

    /**
     *
     * An Aggregate Mapper locater.
     *
     * @var AggregateMapperLocator
     *
     */
    protected $aggregate_mapper_locator;

    /**
     *
     * Our glue between Mappers and our Aggregate Mapper.
     *
     * @var DbMediatorInterface
     *
     */
    protected $db_mediator;

    /**
     *
     * Constructor
     *
     * @param AggregateMapperLocator $aggregate_mapper_locator The locator for
     * Aggregate Mappers.
     *
     * @param DbMediatorInterface $db_mediator Our query and unit-of-work
     * generator.
     *
     */
    public function __construct(
        AggregateMapperLocator $aggregate_mapper_locator,
        DbMediatorInterface $db_mediator
    ) {
        $this->aggregate_mapper_locator = $aggregate_mapper_locator;
        $this->db_mediator = $db_mediator;
    }

    /**
     *
     * Returns a collection of the specified aggregate, each member of
     * which matches the provided criteria.
     *
     * @param  string $aggregate_mapper_name The key of the aggregate_mapper.
     *
     * @param  array  $criteria An array of criteria, describing the objects
     * to be returned.
     *
     * @return mixed An instance of the aggregate collection, as defined by
     * the AggregateMapper
     *
     */
    public function getCollection($aggregate_mapper_name, array $criteria = null)
    {
        $aggregate_mapper = $this->getAggregateMapper($aggregate_mapper_name);
        return $aggregate_mapper->newCollection($this->select($aggregate_mapper_name, $criteria));
    }

    /**
     *
     * Returns a single instance of the specified aggregate that matches
     * the provided criteria.
     *
     * @param string $aggregate_mapper_name The key of the aggregate_mapper.
     *
     * @param array $criteria An array of criteria, describing the object
     * to be returned.
     *
     * @return mixed An instance of the aggregate, as defined by the
     * AggregateMapper
     *
     */
    public function getObject($aggregate_mapper_name, array $criteria = null)
    {
        $aggregate_mapper = $this->getAggregateMapper($aggregate_mapper_name);
        return $aggregate_mapper->newObject($this->select($aggregate_mapper_name, $criteria));
    }

    /**
     *
     * Executes a select for all of the mappers in the indicated
     * aggregate_mapper.
     *
     * @param string $aggregate_mapper_name The key of the aggregate_mapper.
     *
     * @param array $criteria An array of criteria, describing (from the
     * object's perspective) the data to return.
     *
     * @return array An arranged array of arranged DB output.
     *
     */
    public function select($aggregate_mapper_name, array $criteria = null)
    {
        $aggregate_mapper = $this->getAggregateMapper($aggregate_mapper_name);
        return $this->db_mediator->select($aggregate_mapper, $criteria);
    }

    /**
     *
     * Executes an update for the provided object.
     *
     * @param string $aggregate_mapper_name The key of the aggregate_mapper.
     *
     * @param mixed $object The aggregate instance to update.
     *
     * @return bool Whether or not the update was successful.
     *
     */
    public function update($aggregate_mapper_name, $object)
    {
        $aggregate_mapper = $this->getAggregateMapper($aggregate_mapper_name);
        return (bool) $this->db_mediator->update($aggregate_mapper, $object);
    }

    /**
     *
     * Executes an save for the provided object.
     *
     * @param string $aggregate_mapper_name The key of the aggregate_mapper.
     *
     * @param mixed $object The aggregate instance to save.
     *
     * @return bool Whether or not the create was successful.
     *
     */
    public function create($aggregate_mapper_name, $object)
    {
        $aggregate_mapper = $this->getAggregateMapper($aggregate_mapper_name);
        return (bool) $this->db_mediator->create($aggregate_mapper, $object);
    }

    /**
     *
     * Executes a delete for the provided object.
     *
     * @param string $aggregate_mapper_name The key of the aggregate_mapper.
     *
     * @param mixed $object The aggregate instance to delete.
     *
     * @return bool Whether or not the delete was successful.
     *
     */
    public function delete($aggregate_mapper_name, $object)
    {
        $aggregate_mapper = $this->getAggregateMapper($aggregate_mapper_name);
        return (bool) $this->db_mediator->delete($aggregate_mapper, $object);
    }

    /**
     *
     * Resolves an aggregate mapper name to its mapper.
     *
     * @param string $aggregate_mapper_name The name of the map to retrieve.
     *
     * @return AbstractAggregateMapper
     *
     */
    protected function getAggregateMapper($aggregate_mapper_name)
    {
        return $this->aggregate_mapper_locator[$aggregate_mapper_name];
    }
}