<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\MapperInterface;
use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\Query\AbstractConnectedQuery;

class SelectIdentifierCallback implements SelectCallbackInterface
{
    /** @var MapperLocator */
    protected $locator;

    /** @var OperationArranger */
    protected $arranger;

    /** @var AggregateMapperInterface */
    protected $mapper;

    /** @var PlaceholderResolver */
    protected $resolver;

    public function __construct(
        AggregateMapperInterface $mapper,
        MapperLocator $locator,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        $this->locator  = $locator;
        $this->arranger = $arranger;
        $this->mapper   = $mapper;
        $this->resolver = $resolver;
    }

    public function __invoke(array $path)
    {
        $relation_to_mapper = $this->mapper->getRelationToMapper();
        $root_mapper = $this->locator->__get($relation_to_mapper['__root']['mapper']);
        $root_primary = $root_mapper->getIdentityField();
        $ids = [];
        foreach ($path as $node) {
            $row_mapper = $this->locator->__get($relation_to_mapper[$node->relation_name]['mapper']);
            $criteria = $node->criteria;
            if ($criteria === null) {
                $query = $row_mapper->select([$root_primary]);
                $ids['__root'] = $this->runQuery($query, $row_mapper);
            } else {
                $query = $row_mapper->selectBy(
                    key($criteria),
                    $this->resolver->resolve(current($criteria), $ids, $this->mapper),
                    array_merge(
                        $node->fields,
                        [$row_mapper->getIdentityField()]
                    )
                );
                $ids[$node->relation_name] = $this->runQuery($query, $row_mapper);
            }
        }
        return $ids;
    }

    protected function runQuery(AbstractConnectedQuery $query, MapperInterface $mapper)
    {
        return $mapper->getWriteConnection()->fetchAll($query->__toString(), $query->getBindValues());
    }
}