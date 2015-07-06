<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\RowMapperInterface;
use Aura\SqlMapper_Bundle\RowMapperLocator;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\Query\AbstractConnectedQuery;
use Aura\SqlMapper_Bundle\RowObjectBuilder;

/**
 *
 * Used to select Primary keys and foreign keys, not row data objects.
 *
 */
class SelectIdentifierCallback implements SelectCallbackInterface
{
    /** @var RowObjectBuilder */
    protected $row_builder;

    /** @var OperationArranger */
    protected $arranger;

    /** @var AggregateMapperInterface */
    protected $mapper;

    /** @var PlaceholderResolver */
    protected $resolver;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        AggregateMapperInterface $mapper,
        RowObjectBuilder $row_builder,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        $this->row_builder = $row_builder;
        $this->arranger    = $arranger;
        $this->mapper      = $mapper;
        $this->resolver    = $resolver;
    }

    /**
     *
     * Traverses the given path and only selects primary and foreign keys.
     *
     * {@inheritdoc}
     */
    public function __invoke(array $path)
    {
        $relation_to_mapper = $this->mapper->getRelationToMapper();
        $root_mapper = $this->row_builder->getRowMapper($relation_to_mapper['__root']['mapper']);
        $root_primary = $root_mapper->getIdentityField();
        $ids = [];
        foreach ($path as $node) {
            $row_mapper = $this->row_builder->getRowMapper($relation_to_mapper[$node->relation_name]['mapper']);
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

    protected function runQuery(AbstractConnectedQuery $query, RowMapperInterface $mapper)
    {
        return $mapper->getWriteConnection()->fetchAll($query->__toString(), $query->getBindValues());
    }
}