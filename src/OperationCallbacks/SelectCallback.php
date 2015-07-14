<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;
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

    /** @var AggregateMapperInterface */
    protected $mapper;

    /** @var OperationArranger */
    protected $arranger;

    /** @var PlaceholderResolver */
    protected $resolver;

    /**
     * @param AggregateMapperInterface $mapper
     *
     * @param EntityRepository $entity_repository
     *
     * @param OperationArranger $arranger
     *
     * @param PlaceholderResolver $resolver
     *
     */
    public function __construct(
        AggregateMapperInterface $mapper,
        EntityRepository $entity_repository,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        $this->entity_repository = $entity_repository;
        $this->mapper = $mapper;
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
        $relation_to_mapper = $this->mapper->getRelationToMapper();
        foreach ($path as $node) {
            $relation_name = $node->relation_name;
            $mapper_name = $relation_to_mapper[$relation_name]['mapper'];
            $vals = $this->resolver->resolveCriteria($node->criteria, $results, $this->mapper);
            $results[$relation_name] = $this->entity_repository->fetchCollection($mapper_name, $vals);
        }
        return $results;
    }
}