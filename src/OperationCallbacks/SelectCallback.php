<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;

class SelectCallback implements SelectCallbackInterface
{
    /** @var MapperLocator */
    protected $locator;

    /** @var AggregateMapperInterface */
    protected $mapper;

    /** @var OperationArranger */
    protected $arranger;

    /** @var PlaceholderResolver */
    protected $resolver;

    public function __construct(
        AggregateMapperInterface $mapper,
        MapperLocator $locator,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        $this->locator  = $locator;
        $this->mapper   = $mapper;
        $this->arranger = $arranger;
        $this->resolver = $resolver;
    }

    public function __invoke(array $path)
    {
        $results = [];
        $relation_to_mapper = $this->mapper->getRelationToMapper();
        foreach ($path as $node) {
            $relation_name = $node->relation_name;
            $mapper_name = $relation_to_mapper[$relation_name]['mapper'];
            $row_mapper = $this->locator->__get($mapper_name);
            $primary_field = key($node->criteria);
            $vals = $this->resolver->resolve(current($node->criteria), $results, $this->mapper);
            $results[$relation_name] = $row_mapper->fetchCollectionBy($primary_field, $vals);
        }
        return $results;
    }
}