<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;
use Aura\SqlMapper_Bundle\Relations\RelationLocator;

/**
 *
 * Knows how to determine the order of entities and create an ordered list of operation objects.
 *
 * In charge of ordering the DB operations to execute based on a relation map. For many cases, we will need to wait for
 * one DB operation to finish before we can determine the value or a where clause. This class will insert place holders
 * that can be used to traverse the results array to find the appropriate values.
 *
 */
class OperationManager
{
    /** @var EntityOperationFactory */
    protected $operation_factory;

    /** @var RelationLocator */
    protected $locator;

    /** @var PlaceHolderFactory  */
    protected $placeholder_factory;

    /**
     *
     * Constructor.
     *
     * @param EntityOperationFactory $factory
     *
     * @param PlaceHolderFactory $placeHolder_factory
     *
     * @param RelationLocator $locator
     *
     */
    public function __construct(
        EntityOperationFactory $factory,
        PlaceHolderFactory $placeHolder_factory,
        RelationLocator $locator
    ) {
        $this->factory = $factory;
        $this->locator = $locator;
        $this->placeholder_factory = $placeHolder_factory;
    }

    public function getOrder(AggregateBuilderInterface $builder)
    {
        $order = [];
        $owning_sides = [];
        foreach ($builder->getRelations() as $relation_name) {
            $relation = $this->locator->__get($relation_name);
            $inverse_entity = $relation->getInverseEntity();
            $owning_entity = $relation->getOwningEntity();
            $has_owning = array_search($owning_entity, $order);
            if ($has_owning == true) {
                $piece = array_splice($order, $has_owning, count($order));
                array_unshift($piece, $inverse_entity);
                $order = array_merge($order, $piece);
            } else {
                $owning_sides[] = $owning_entity;
                $order[] = $inverse_entity;
            }
        }
        foreach ($owning_sides as $owning_side) {
            if (array_search($owning_side, $order) === false) {
                $order[] = $owning_side;
            }
        }
        return $order;
    }
}