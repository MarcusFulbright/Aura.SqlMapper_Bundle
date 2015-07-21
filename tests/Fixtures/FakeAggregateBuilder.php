<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Aggregate\AbstractAggregateBuilder;

class FakeAggregateBuilder extends AbstractAggregateBuilder
{
    protected $entities;

    protected $aggregates;

    protected $root;

    protected $relations;


    public function getEntities()
    {
        return $this->entities;
    }

    public function getAggregates()
    {
        return $this->aggregates;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param mixed $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @param mixed $aggregates
     */
    public function setAggregates($aggregates)
    {
        $this->aggregates = $aggregates;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @param mixed $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
    }
}