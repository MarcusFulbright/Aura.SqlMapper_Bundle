<?php
namespace Aura\SqlMapper_Bundle\Aggregate;

use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlMapper_Bundle\ObjectFactoryInterface;

abstract class AbstractAggregateBuilder implements AggregateBuilderInterface
{
    /** @var ObjectFactoryInterface */
    protected $factory;

    /** @var Filter */
    protected $filter;

    /** {@inheritdoc} */
    public function __construct(
        $factory,
        Filter $filter
    ) {
        $this->factory = $factory;
        $this->filter = $filter;
    }

    /** {@inheritdoc} */
    abstract public function getRelations();

    /** {@inheritdoc} */
    abstract public function getRoot();

    /** {@inheritdoc} */
    abstract public function getEntities();

    /** {@inheritdoc} */
    abstract public function getAggregates();

    /** {@inheritdoc} */
    public function deconstructAggregate($aggregate_object, $insert = false)
    {
        if ($insert === true) {
            return $this->filter->forInsert($aggregate_object);
        } else {
            return $this->filter->forUpdate($aggregate_object);
        }
    }

    /** {@inheritdoc} */
    public function newCollection(array $entities)
    {
        return $this->factory->newCollection($entities);
    }

    /** {@inheritdoc} */
    public function newObject(array $entities)
    {
        return $this->factory->newObject($entities);
    }
}