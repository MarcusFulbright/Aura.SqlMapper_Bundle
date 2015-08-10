<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;
<<<<<<< HEAD
use Aura\SqlMapper_Bundle\Relations\Relation;
=======
>>>>>>> 5fa0775e710b72959ceb4ecd770cbca2d0945f8e
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
<<<<<<< HEAD
        $this->operation_factory = $factory;
=======
        $this->factory = $factory;
>>>>>>> 5fa0775e710b72959ceb4ecd770cbca2d0945f8e
        $this->locator = $locator;
        $this->placeholder_factory = $placeHolder_factory;
    }

    public function getOrder(AggregateBuilderInterface $builder)
    {
<<<<<<< HEAD
        //set both inverse and owning side
        //if inverse side has been previously inserted as owning side, remove it from that relation's entities list and put it at the end of the array
        $order = [];
        $inserted = [];
        foreach ($builder->getRelations() as $relation_name) {
            $relation = $this->locator->__get($relation_name);
            $inverse = $relation->getInverseEntity();
            $owning = $relation->getOwningEntity();
            if (array_key_exists($inverse, $inserted)) {

            }






            $order[] = [
                $relation_name => [
                    'entities' => [$entities],
                    'relation' => $relation
                ]
            ];
            $inserted[$owning] = $relation_name;
        }
        return $order;
    }

    public function getOperationList(array $order, array $pieces)
    {
        $operations = [];
        foreach ($order as $order_entity => $order_relation) {
            foreach ($pieces as $entities) {
                foreach ($entities as $piece_entity => $instance) {
                    if ($piece_entity === $order_entity) {
                        $criteria = $this->getCriteria($entities, $order_relation, $order_entity);
                        $operation = $this->operation_factory->newOperation($order_entity, $instance, $criteria);
                        $operations[$order_entity][] = $operation;
                    }
                }
            }
        }
        return $operations;
    }

    public function getCriteria(array $entities, Relation $relation = null, $from)
    {
        if ($relation === null) {
            return [];
        }
        $inverse = $relation->getInverseEntity();
        $owner = $relation->getOwningEntity();
        if ($from === $inverse) {
            $func = $this->placeholder_factory->getObjectPlaceHolder($entities[$owner], $relation->getOwningField());
            $criteria = [$relation->getInverseField() => $func];
        } else {
            $func = $this->placeholder_factory->getObjectPlaceHolder($entities[$inverse], $relation->getInverseField());
            $criteria = [$relation->getOwningField() => $func];
        }
        return $criteria;
=======
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
>>>>>>> 5fa0775e710b72959ceb4ecd770cbca2d0945f8e
    }
}