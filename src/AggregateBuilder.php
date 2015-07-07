<?php
namespace Aura\SqlMapper_Bundle;

class AggregateBuilder implements BuilderInterface
{
    /**
     *
     * An Aggregate Mapper locator.
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
     * Arranges the db mediator output for factory injest.
     *
     * @var RowDataArrangerInterface
     *
     */
    protected $row_data_arranger;

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
     * @param RowDataArrangerInterface $row_data_arranger The arranger for row
     * data output by the DbMediator
     *
     */
    public function __construct(
        AggregateMapperLocator $aggregate_mapper_locator,
        DbMediatorInterface $db_mediator,
        RowDataArrangerInterface $row_data_arranger
    ) {
        $this->aggregate_mapper_locator = $aggregate_mapper_locator;
        $this->db_mediator = $db_mediator;
        $this->row_data_arranger = $row_data_arranger;
    }

    /**
     *
     * Returns a collection of the specified aggregate, each member of
     * which matches the provided criteria.
     *
     * @param  string $mapper_name The key of the aggregate_mapper.
     *
     * @param  array  $criteria An array of criteria, describing the objects
     * to be returned.
     *
     * @return mixed An instance of the aggregate collection, as defined by
     * the AggregateMapper
     *
     */
    public function fetchCollection($mapper_name, array $criteria = array())
    {
        $aggregate_mapper = $this->getMapper($mapper_name);
        return $aggregate_mapper->newCollection($this->select($mapper_name, $criteria));
    }

    /**
     *
     * Returns a single instance of the specified aggregate that matches
     * the provided criteria.
     *
     * @param string $mapper_name The key of the aggregate_mapper.
     *
     * @param array $criteria An array of criteria, describing the object
     * to be returned.
     *
     * @return mixed An instance of the aggregate, as defined by the
     * AggregateMapper
     *
     */
    public function fetchObject($mapper_name, array $criteria = array())
    {
        $aggregate_mapper = $this->getMapper($mapper_name);
        $results = $this->select($mapper_name, $criteria);
        return $results ? $aggregate_mapper->newObject($results[0]) : false;
    }

    /**
     *
     * Executes a select for all of the mappers in the indicated
     * aggregate_mapper.
     *
     * @param string $mapper_name The key of the aggregate_mapper.
     *
     * @param array $criteria An array of criteria, describing (from the
     * object's perspective) the data to return.
     *
     * @return array An arranged array of arranged DB output.
     *
     */
    public function select($mapper_name, array $criteria = array())
    {
        $aggregate_mapper = $this->getMapper($mapper_name);
        return $this->row_data_arranger->arrangeRowData(
            $this->db_mediator->select($aggregate_mapper, $criteria),
            $aggregate_mapper
        );
    }

    /**
     *
     * Executes an update for the provided object.
     *
     * @param string $mapper_name The key of the aggregate_mapper.
     *
     * @param mixed $object The aggregate instance to update.
     *
     * @return bool Whether or not the update was successful.
     *
     */
    public function update($mapper_name, $object)
    {
        $aggregate_mapper = $this->getMapper($mapper_name);
        return (bool) $this->db_mediator->update($aggregate_mapper, $object);
    }

    /**
     *
     * Executes an save for the provided object.
     *
     * @param string $mapper_name The key of the aggregate_mapper.
     *
     * @param mixed $object The aggregate instance to save.
     *
     * @return bool Whether or not the create was successful.
     *
     */
    public function create($mapper_name, $object)
    {
        $aggregate_mapper = $this->getMapper($mapper_name);
        return (bool) $this->db_mediator->create($aggregate_mapper, $object);
    }

    /**
     *
     * Executes a delete for the provided object.
     *
     * @param string $mapper_name The key of the aggregate_mapper.
     *
     * @param mixed $object The aggregate instance to delete.
     *
     * @return bool Whether or not the delete was successful.
     *
     */
    public function delete($mapper_name, $object)
    {
        $aggregate_mapper = $this->getMapper($mapper_name);
        return (bool) $this->db_mediator->delete($aggregate_mapper, $object);
    }

    /**
     *
     * Resolves an aggregate mapper name to its mapper.
     *
     * @param string $mapper_name The name of the map to retrieve.
     *
     * @return AbstractAggregateMapper || false
     *
     */
    public function getMapper($mapper_name)
    {
        if ($this->aggregate_mapper_locator->offsetExists($mapper_name)) {
            return $this->aggregate_mapper_locator[$mapper_name];
        } else {
            return false;
        }
    }
}