<?php
namespace Aura\SqlMapper_Bundle;

/**
 *
 * Knows how to determine the order of operations.
 *
 * In charge of ordering the DB operations to execute based on a relation map. For many cases, we will need to wait for
 * one DB operation to finish before we can determine the value or a where clause. This class will insert place holders
 * that can be used to traverse the results array to find the appropriate values.
 *
 */
class OperationArranger
{
    /**
     *
     * Returns a list of arrays that represent all of the context to perform a DB operation in the correct execution
     * order.
     *
     * @param AggregateMapperInterface $map
     *
     * @param array|null $criteria
     *
     * @return array
     */
    public function arrangeForSelect(AggregateMapperInterface $map, array $criteria = null)
    {
        $entry_context = $this->getEntryContext($criteria);
        return $this->getOrderedOperations($entry_context, $map);
    }

    /**
     * Used to determine an entry point into the object graph based on the given criteria
     *
     * @param $criteria
     *
     * @return \stdClass
     *
     */
    protected function getEntryContext(array $criteria)
    {
        $context = new \stdClass();
        if ($criteria != null) {
            $entry_pieces      = explode('.', key($criteria));
            $context->field    = array_pop($entry_pieces);
            $context->property = implode('.', $entry_pieces);
            $context->value    = current($criteria);
        } else {
            $context->property = '__root';
            $context->value = null;
        }
        return $context;
    }

    /**
     * Traverses the object map based on the given entry context to build an order of operations
     *
     * @param $entry_context
     *
     * @param AggregateMapperInterface $map
     *
     * @return array
     */
    protected function getOrderedOperations($entry_context, AggregateMapperInterface $map)
    {
        $relation_to_mapper = $map->getRelationToMapper();
        $operations = array();
        $operation  = $relation_to_mapper[$entry_context->property];
        if ($entry_context->value != null) {
            $operation['criteria'] = array(
               $entry_context->field => $entry_context->value
            );
        }
        $operations[$entry_context->property] = $operation;
        foreach($operation['relations'] as $relation) {
            $operations = array_merge(
                $operations,
                $this->handleRelation($relation, $entry_context->property, $map)
            );
        }
        return $operations;
    }

    /**
     * Knows how to build the operation context for a particular relation and will recurse if necessary
     *
     * @param $relation
     * @param $from
     * @param AggregateMapperInterface $map
     * @param array $visited
     * @return array
     */
    protected function handleRelation($relation, $from, AggregateMapperInterface $map, &$visited = array())
    {
        $relation_to_mapper = $map->getRelationToMapper();
        $relation_map = $map->getRelationMap();
        $operations = array();
        $operation  = $relation_to_mapper[$relation['other_side']];
        $relation_info = $relation_map[$relation['relation_name']];
        $criteria = $this->getPlaceHolder($relation_info, $relation, $from, $map);
        $operation['criteria'] = $criteria;
        $visited[] = $relation['relation_name'];
        $operations[$relation['other_side']] = $operation;
        foreach ($operation['relations'] as $next_relation) {
            if (in_array($next_relation['relation_name'], $visited)) {
                continue;
            }
            $operations = array_merge(
                $operations,
                $this->handleRelation($next_relation, $relation['other_side'], $map, $visited)
            );
        }
        return $operations;
    }


    /**
     * Determines the criteria for operations further down the chain.
     *
     * A key value pair of Name, the aggregates property, and the value, the property address to the values.
     *
     * @param $relation_info
     * @param $relation
     * @param $from
     * @param AggregateMapperInterface $map
     * @return array
     */
    protected function getPlaceHolder($relation_info, $relation, $from, AggregateMapperInterface $map)
    {
        if ($relation['other_side'] === $relation['relation_name']) {
            $value = ':' . $from . '.' . $relation_info['reference'];
            $mapper_address = $map->separateMapperFromField(
                $map->getPropertyMap()[$relation['relation_name'] . '.'. $relation_info['joinProperty']]
            );
        } else {
            /*
             * This could be eliminated if AggregateMapper->getPropertyMap included a __root prefix
             */
            if ($relation['other_side'] === '__root') {
                $mapper_address = $map->separateMapperFromField(
                    $map->getPropertyMap()[$relation_info['reference']]
                );
            } else {
                $mapper_address = $map->separateMapperFromField(
                    $map->getPropertyMap()[$relation['other_side'] . '.' . $relation_info['reference']]
                );
            }
            $value = ':' . $relation['relation_name'] . '.' . $relation_info['joinProperty'];
        }
        $name = $mapper_address->field;
        return array($name => $value);
    }
 }