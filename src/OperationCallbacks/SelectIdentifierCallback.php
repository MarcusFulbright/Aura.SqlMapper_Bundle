<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;


use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\Entity\EntityMapperInterface;
use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\EntityMediation\OperationArranger;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver;
use Aura\SqlMapper_Bundle\Query\AbstractConnectedQuery;

/**
 *
 * Used to select Primary keys and foreign keys, not row data objects.
 *
 */
class SelectIdentifierCallback implements SelectCallbackInterface
{
    /** @var EntityRepository */
    protected $entity_repository;

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
        EntityRepository $entity_repository,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    ) {
        $this->entity_repository = $entity_repository;
        $this->arranger = $arranger;
        $this->mapper = $mapper;
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
        $relation_to_mapper = $this->mapper->getRelationToMapper();
        $root_mapper = $this->entity_repository->getMapper($relation_to_mapper['__root']['mapper']);
        $root_primary = $root_mapper->getIdentityField();
        $ids = [];
        foreach ($path as $node) {
            $entity_mapper = $this->entity_repository->getMapper($relation_to_mapper[$node->relation_name]['mapper']);
            $criteria = $node->criteria;
            if ($criteria === null) {
                $query = $entity_mapper->select([$root_primary]);
                $ids['__root'] = $this->runQuery($query, $entity_mapper);
            } else {
                $query = $entity_mapper->selectBy(
                    $this->resolver->resolveCriteria($criteria, $ids, $this->mapper),
                    array_merge(
                        $node->fields,
                        [$entity_mapper->getIdentityField()]
                    )
                );
                $ids[$node->relation_name] = $this->runQuery($query, $entity_mapper);
            }
        }
        return $ids;
    }

    protected function runQuery(AbstractConnectedQuery $query, EntityMapperInterface $mapper)
    {
        return $mapper->getWriteConnection()->fetchAll($query->__toString(), $query->getBindValues());
    }
}