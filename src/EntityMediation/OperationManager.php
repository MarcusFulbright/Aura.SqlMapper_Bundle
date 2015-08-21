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
    /** @var RelationLocator */
    protected $locator;

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
     */
    public function __construct(
        PlaceHolderFactory $placeHolder_factory,
        RelationLocator $locator,
        EntityOperationFactory $operation_factory
    ) {
        $this->locator = $locator;
        $this->placeholder_factory = $placeHolder_factory;
        $this->operation_factory = $operation_factory;
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
            $order = $this->arrangeOrder($item, $order);
        }
        return $order;
    }

    protected function arrangeOrder(\stdClass $item, array $order)
    {
        $entities = $item->entities;
        $altered = false;
        foreach ($order as $index => $ordered_item) {
            $has_owning = array_key_exists('owning', $ordered_item->entities);
            if ($has_owning && $ordered_item->entities['owning'] === $entities['inverse']) {
                unset($ordered_item->entities['owning']);
                $order[$index] = $ordered_item;
                $order[] = $item;
                $altered = true;
            } elseif ($has_owning && $ordered_item->entities['inverse'] === $entities['owning']) {
                unset($item->entities['owning']);
                $order[$index] = $item;
                $order[] = $ordered_item;
                $altered = true;
            }
        }
        if ($altered === false) {
            $order[] = $item;
        }
        return $order;
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
                    [$own_field => $this->placeholder_factory->newObjectPlaceHolder($entity[$inverse], $inv_field)]
                );
            }
        }
        return $operations;
    }
}