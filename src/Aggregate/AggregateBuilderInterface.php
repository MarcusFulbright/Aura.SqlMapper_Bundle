<?php
namespace Aura\SqlMapper_Bundle\Aggregate;

use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Relations\Relation;

interface AggregateBuilderInterface
{
    /**
     *
     * Constructor.
     *
     * @param ObjectFactoryInterface $aggregate_factory
     *
     * @param Filter $filter
     *
     */
    public function __construct($aggregate_factory, Filter $filter);

    /**
     *
     * Create a collection from an array of rows.
     *
     * @param array $entities All of the entities required to create a collection of Aggregates
     *
     * @return mixed
     *
     */
    public function newCollection(array $entities);

    /**
     *
     * Create an AggregateDomain object from a single row.
     *
     * @param array $entities All of the entities that compose a single Aggregate instance
     *
     * @return mixed
     *
     */
    public function newObject(array $entities);

    /**
     *
     * Returns all RelationObjects indexed by their name.
     *
     * @return array
     *
     */
    public function getRelations();

    /**
     *
     * Returns the root entity or aggregate.
     *
     * @return Relation
     *
     */
    public function getRoot();

    /**
     *
     * Returns an array of all the entities that compose this object.
     *
     * @return array
     *
     */
    public function getEntities();

    /**
     *
     * Returns an array of all the aggregates that compose this object.
     *
     * @return array
     *
     */
    public function getAggregates();

    /**
     *
     * Takes an Aggregate object and returns an array of entity objects indexed by the entity's relation name.
     *
     * @param object $aggregate_object the aggregate to operate on.
     *
     * @param bool $insert If true the builder's filter forInsert will get called, otherwise forUpdate is used.
     *
     * @return array
     *
     */
    public function deconstructAggregate($aggregate_object, $insert = false);
}