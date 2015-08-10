<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;
use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\EntityMediation\OperationArranger;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver;

/**
 *
 * Selects row data objects based on the supplied OperationContext objects in the given order.
 *
 */
class SelectCallback implements SelectCallbackInterface
{
    /** @var EntityRepository */
    protected $entity_repository;

    /** @var AggregateBuilderInterface */
    protected $builder;

    /** @var OperationArranger */
    protected $arranger;

    /** @var PlaceholderResolver */
    protected $resolver;

    /**
     * @param AggregateBuilderInterface $builder
     *
     * @param EntityRepository $entity_repository
     *
     * @param OperationArranger $arranger
     *
     * @param PlaceholderResolver $resolver
     *
     */
    public function __construct(
        AggregateBuilderInterface $builder,
        EntityRepository $entity_repository,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        $this->entity_repository = $entity_repository;
        $this->builder = $builder;
        $this->arranger = $arranger;
        $this->resolver = $resolver;
    }

    /**
     *
     * Goes down the given $path selecting all of the row data objects described by given context
     *
     * @param array $path An array of OperationContext objects in the correct order for traversal
     *
     * @return array
     *
     */
    public function __invoke(array $path)
    {
        $results = [];
        foreach ($path as $node) {
            $relation_name = $node->relation_name;
            $vals = $this->resolver->resolveCriteria($node->criteria, $results, $this->builder);
            $results[$relation_name] = $this->entity_repository->fetchCollection($relation_name, $vals);
        }
        return $results;
    }
}