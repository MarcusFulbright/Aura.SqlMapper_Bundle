<?php
namespace Aura\Sqlbuilder_Bundle\Aggregate;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;
use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderLocator;
use Aura\SqlMapper_Bundle\EntityMediation\EntityArrangerInterface;
use Aura\SqlMapper_Bundle\EntityMediation\EntityMediatorInterface;
use Aura\SqlMapper_Bundle\RepositoryInterface;

class AggregateRepository implements RepositoryInterface
{
    /**
     *
     * An Aggregate builder locator.
     *
     * @var AggregateBuilderLocator
     *
     */
    protected $aggregate_builder_locator;

    /**
     *
     * Our glue between builders and our Aggregate builder.
     *
     * @var EntityMediatorInterface
     *
     */
    protected $entity_mediator;

    /**
     *
     * Arranges the db mediator output for factory injest.
     *
     * @var EntityArrangerInterface
     *
     */
    protected $entity_arranger;

    /**
     *
     * Constructor
     *
     * @param AggregateBuilderLocator $aggregate_builder_locator The locator for
     * Aggregate builders.
     *
     * @param EntityMediatorInterface $entity_mediator Our query and unit-of-work
     * generator.
     *
     * @param EntityArrangerInterface $entity_arranger The arranger for row
     * data output by the DbMediator
     *
     */
    public function __construct(
        AggregateBuilderLocator $aggregate_builder_locator,
        EntityMediatorInterface $entity_mediator,
        EntityArrangerInterface $entity_arranger
    ) {
        $this->aggregate_builder_locator = $aggregate_builder_locator;
        $this->entity_mediator = $entity_mediator;
        $this->entity_arranger = $entity_arranger;
    }

    /**
     *
     * Returns a collection of the specified aggregate, each member of
     * which matches the provided criteria.
     *
     * @param  string $builder_name The key of the aggregate_builder.
     *
     * @param  array  $criteria An array of criteria, describing the objects
     * to be returned.
     *
     * @return mixed An instance of the aggregate collection, as defined by
     * the AggregateBuilder
     *
     */
    public function fetchCollection($builder_name, array $criteria = [])
    {
        $aggregate_builder = $this->getbuilder($builder_name);
        return $aggregate_builder->newCollection($this->select($builder_name, $criteria));
    }

    /**
     *
     * Returns a single instance of the specified aggregate that matches
     * the provided criteria.
     *
     * @param string $builder_name The key of the aggregate_builder.
     *
     * @param array $criteria An array of criteria, describing the object
     * to be returned.
     *
     * @return mixed An instance of the aggregate, as defined by the
     * Aggregatebuilder
     *
     */
    public function fetchObject($builder_name, array $criteria = array())
    {
        $aggregate_builder = $this->getbuilder($builder_name);
        $results = $this->select($builder_name, $criteria);
        return $results ? $aggregate_builder->newObject($results[0]) : false;
    }

    /**
     *
     * Executes a select for all of the builders in the indicated
     * aggregate_builder.
     *
     * @param string $builder_name The key of the aggregate_builder.
     *
     * @param array $criteria An array of criteria, describing (from the
     * object's perspective) the data to return.
     *
     * @return array An arranged array of arranged DB output.
     *
     */
    public function select($builder_name, array $criteria = [])
    {
        $aggregate_builder = $this->getbuilder($builder_name);
        return $this->entity_arranger->arrangeRowData(
            $this->entity_mediator->select($aggregate_builder, $criteria),
            $aggregate_builder
        );
    }

    /**
     *
     * Executes an update for the provided object.
     *
     * @param string $builder_name The key of the aggregate_builder.
     *
     * @param mixed $object The aggregate instance to update.
     *
     * @return bool Whether or not the update was successful.
     *
     */
    public function update($builder_name, $object)
    {
        $aggregate_builder = $this->getbuilder($builder_name);
        return (bool) $this->entity_mediator->update($aggregate_builder, $object);
    }

    /**
     *
     * Executes an save for the provided object.
     *
     * @param string $builder_name The key of the aggregate_builder.
     *
     * @param mixed $object The aggregate instance to save.
     *
     * @return bool Whether or not the create was successful.
     *
     */
    public function create($builder_name, $object)
    {
        $aggregate_builder = $this->getbuilder($builder_name);
        return (bool) $this->entity_mediator->create($aggregate_builder, $object);
    }

    /**
     *
     * Executes a delete for the provided object.
     *
     * @param string $builder_name The key of the aggregate_builder.
     *
     * @param mixed $object The aggregate instance to delete.
     *
     * @return bool Whether or not the delete was successful.
     *
     */
    public function delete($builder_name, $object)
    {
        $aggregate_builder = $this->getbuilder($builder_name);
        return (bool) $this->entity_mediator->delete($aggregate_builder, $object);
    }

    /**
     *
     * Resolves an aggregate builder name to its builder.
     *
     * @param string $builder_name The name of the map to retrieve.
     *
     * @return AggregateBuilderInterface || false
     *
     */
    public function getBuilder($builder_name)
    {
        if ($this->aggregate_builder_locator->offsetExists($builder_name)) {
            return $this->aggregate_builder_locator[$builder_name];
        } else {
            return false;
        }
    }
}