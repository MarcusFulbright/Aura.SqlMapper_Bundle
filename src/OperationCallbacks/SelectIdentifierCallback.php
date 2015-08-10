<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;
use Aura\SqlMapper_Bundle\Entity\EntityMapperInterface;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver;
use Aura\SqlMapper_Bundle\Query\AbstractConnectedQuery;

/**
 *
 * Used to select Primary keys and foreign keys, not row data objects.
 *
 */
class SelectIdentifierCallback implements SelectCallbackInterface
{
    /** @var AggregateBuilderInterface */
    protected $builder;

    /** @var PlaceholderResolver */
    protected $resolver;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        AggregateBuilderInterface $builder,
        PlaceholderResolver $resolver
    ) {
        $this->builder = $builder;
        $this->resolver = $resolver;
    }

    /**
     *
     * Traverses the given path and only selects primary and foreign keys.
     *
     * {@inheritdoc}
     */
    public function __invoke(array $path)
    {

    }

    protected function runQuery(AbstractConnectedQuery $query, EntityMapperInterface $mapper)
    {
        return $mapper->getWriteConnection()->fetchAll($query->__toString(), $query->getBindValues());
    }
}