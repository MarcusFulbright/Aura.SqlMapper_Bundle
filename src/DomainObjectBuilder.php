<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Exception\NoSuchMapper;

class DomainObjectBuilder implements BuilderInterface
{
    /** @var AggregateBuilder */
    protected $aggregate_builder;

    /** @var RowObjectBuilder */
    protected $row_builder;

    /**
     *
     * Constructor.
     *
     * @param AggregateBuilder $aggregate_builder
     *
     * @param RowObjectBuilder $row_builder
     *
     */
    public function __construct(AggregateBuilder $aggregate_builder, RowObjectBuilder $row_builder)
    {
        $this->aggregate_builder = $aggregate_builder;
        $this->row_builder       = $row_builder;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCollection($mapper_name, array $criteria = [])
    {
        return $this->getBuilder($mapper_name)->fetchCollection($mapper_name, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchObject($mapper_name, array $criteria = [])
    {
        return $this->getBuilder($mapper_name)->fetchObject($mapper_name, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function select($mapper_name, array $criteria = [])
    {
        return $this->getBuilder($mapper_name)->select($mapper_name, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function update($mapper_name, $object)
    {
        return $this->getBuilder($mapper_name)->update($mapper_name, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function create($mapper_name, $object)
    {
        return $this->getBuilder($mapper_name)->create($mapper_name, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($mapper_name, $object)
    {
        return $this->getBuilder($mapper_name)->delete($mapper_name, $object);
    }

    /**
     *
     * Returns the builder associated with the given $mapper_name, in the event of a naming collision, aggregates win
     *
     * {@inheritdoc}
     *
     * @return BuilderInterface
     *
     * @throws NoSuchMapper if $mapper_name is not mapped to an aggregate or row mapper.
     */
    public function getBuilder($mapper_name)
    {
        if ($this->getAggregateMapper($mapper_name) != false) {
            return $this->aggregate_builder;
        } elseif ($this->getRowMapper($mapper_name) != false) {
            return $this->row_builder;
        } else {
            throw new NoSuchMapper("$mapper_name is not defined");
        }
    }

    /**
     *
     * Returns the mapper associated with the given $mapper_name, in the event of a naming collision, aggregates win
     *
     * {@inheritdoc}
     *
     * @return AggregateMapperInterface||RowMapperInterface
     *
     * @throws NoSuchMapper if $mapper_name is not mapped to an aggregate or row mapper.
     *
     */
    public function getMapper($mapper_name)
    {
        if (($aggregate = $this->getAggregateMapper($mapper_name)) != false) {
            return $aggregate;
        } elseif (($row = $this->getRowMapper($mapper_name)) != false) {
            return $row;
        } else {
            throw new NoSuchMapper("$mapper_name is not defined");
        }
    }

    protected function getAggregateMapper($mapper_name)
    {
        return $this->aggregate_builder->getMapper($mapper_name);
    }

    protected function getRowMapper($mapper_name)
    {
        return $this->row_builder->getMapper($mapper_name);
    }
}