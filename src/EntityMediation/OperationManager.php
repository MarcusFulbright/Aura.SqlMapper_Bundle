<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;
use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderLocator;
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
    /** @var RelationLocator */
    protected $locator;

    /** @var AggregateBuilderLocator */
    protected $aggregate_locator;

    /** @var PlaceHolderFactory  */
    protected $placeholder_factory;

    /** @var  EntityOperationFactory */
    protected $operation_factory;

    /**
     *
     * Constructor.
     *
     * @param PlaceHolderFactory $placeHolder_factory
     *
     * @param RelationLocator $locator
     *
     * @param EntityOperationFactory $operation_factory
     *
     * @param AggregateBuilderLocator $aggregate_locator
     *
     */
    public function __construct(
        PlaceHolderFactory $placeHolder_factory,
        RelationLocator $locator,
        EntityOperationFactory $operation_factory,
        AggregateBuilderLocator $aggregate_locator
    ) {
        $this->locator = $locator;
        $this->placeholder_factory = $placeHolder_factory;
        $this->operation_factory = $operation_factory;
        $this->aggregate_locator = $aggregate_locator;
    }

    public function getOrder(AggregateBuilderInterface $builder)
    {
        $order = [];
        foreach ($builder->getRelations() as $relation_name) {
            $relation = $this->locator->__get($relation_name);
            $inverse = $relation->getInverseEntity();
            $owning = $relation->getOwningEntity();
            $item = (object) [
                'relation_name' => $relation_name,
                'relation' => $relation,
                'entities' => [
                    'inverse' => $inverse,
                    'owning' => $owning
                ]
            ];
            $order = $this->arrangeOrder($item, $order, $builder);
        }
        return $order;
    }

    protected function arrangeOrder(\stdClass $item, array $order, AggregateBuilderInterface $builder)
    {
        $entities = $item->entities;
        $aggregates = $builder->getAggregates();
        $owning_aggregate = array_key_exists('owning', $entities) && in_array($entities['owning'], $aggregates);
        $inverse_aggregate = in_array($item->entities['inverse'], $aggregates);

        if ($owning_aggregate) {
            $next = $entities['owning'];
            unset($item->entities['owning']);
            $operations = array_merge([$item], $this->getOrder($this->aggregate_locator->__get($next)));
        } elseif ($inverse_aggregate) {
            $next = $entities['inverse'];
            unset($item->entities['inverse']);
            $operations = array_merge([$item], $this->getOrder($this->aggregate_locator->__get($next)));
        } else {
            $operations = [$item];
        }

        return $this->addOperations($order, $operations);
    }

    protected function addOperations(array $order, array $operations)
    {
        switch(true) {
            case count($order) === 0:
                $order = $operations;
                break;
            case ! array_key_exists('inverse', $operations[0]->entities) && $contains_owning = $this->containsAsInverse($order, $operations[0], 'owning'):
                unset($operations[0]);
                $order = array_merge(
                    array_slice($order, 0, key($contains_owning)),
                    $operations,
                    array_slice($order, key($contains_owning))
                );
                break;
            case $contains_inverse = $this->containsAsOwning($order, $operations[0], 'inverse'):
                $order = array_merge(
                    array_slice($order, 0, key($contains_inverse) + 1),
                    $operations,
                    array_slice($order, key($contains_inverse) + 2)
                );
                break;
            default:
                $order = array_merge($order, $operations);
        }
        return $order;
    }

    protected function containsAsInverse(array $order, \stdClass $operation, $position)
    {
        $check = $operation->entities[$position];
        foreach ($order as $key => $order_item) {
            if (array_key_exists('inverse', $order_item->entities) && $order_item->entities['inverse'] === $check) {
                unset($order_item->entities['owning']);
                return [$key => $order_item];
            }
        }
        return false;
    }

    protected function containsAsOwning(array $order, \stdClass $operation, $position)
    {
        $check = $operation->entities[$position];
        foreach ($order as $key => $order_item) {
            if (array_key_exists('owning', $order_item->entities) && $order_item->entities['owning'] === $check) {
                unset($order_item->entities['owning']);
                return [$key => $order_item];
            }
        }
        return false;
    }

    public function getOperationList(array $order, array $extracted_entities)
    {
        $list = [];
        foreach ($order as $item) {
            $operations = $this->handleEntities($item, $extracted_entities[$item->relation_name]);
            $list = array_merge($list, $operations);
        }
        return $list;
    }

    protected function handleEntities(\stdClass $order_item, array $instances)
    {
        $operations = [];
        $inverse = $order_item->entities['inverse'];
        $owning = isset($order_item->entities['owning']) ? $order_item->entities['owning'] : null;
        $relation  = $order_item->relation;
        $own_field = $relation->getOwningField();
        $inv_field = $relation->getInverseField();
        foreach ($instances as $entity) {
            $operations[] = $this->operation_factory->newOperation($inverse, $entity[$inverse], []);
            if ($owning) {
                $operations[] = $this->operation_factory->newOperation(
                    $owning,
                    $entity[$owning],
                    //place holder logic needs to get smarter and not just look at current relation. 
                    [$own_field => $this->placeholder_factory->newObjectPlaceHolder($entity[$inverse], $inv_field)]
                );
            }
        }
        return $operations;
    }
}