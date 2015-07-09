<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\RowObjectBuilder;


/**
 *
 * Selects row data objects based on the supplied OperationContext objects in the given order.
 *
 */
class SelectCallback implements SelectCallbackInterface
{
    /** @var RowObjectBuilder */
    protected $row_builder;

    /** @var AggregateMapperInterface */
    protected $mapper;

    /** @var OperationArranger */
    protected $arranger;

    /** @var PlaceholderResolver */
    protected $resolver;

    /**
     * @param AggregateMapperInterface $mapper
     *
     * @param RowObjectBuilder $row_builder
     *
     * @param OperationArranger $arranger
     *
     * @param PlaceholderResolver $resolver
     *
     */
    public function __construct(
        AggregateMapperInterface $mapper,
        RowObjectBuilder $row_builder,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        $this->row_builder  = $row_builder;
        $this->mapper   = $mapper;
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
            $results[$relation_name] = $this->row_builder->fetchCollection($mapper_name, $vals);
        }
        return $results;
    }
}