<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;

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
     * Returns an array of context objects describing the dependency tree to resolve the criteria to the root.
     *
     * @param AggregateMapperInterface $mapper
     *
     * @param array $criteria
     *
     * @return array
     *
     */
    public function getPathToRoot(AggregateMapperInterface $mapper, array $criteria = [])
    {
        $entry_context = $this->getNode($criteria, $mapper);
        $path[] = $entry_context;
        $path = $this->traverseRelations($mapper, $entry_context, $path);
        $seen_root = false;
        //only return everything up-to and including __root, nothing after
        return array_filter(
            $path,
            function ($node) use (&$seen_root) {
                switch (true) {
                    case ($seen_root === false && $node->relation_name === '__root'):
                        $seen_root = true;
                    case ($seen_root === false && $node->relation_name !== '__root'):
                        return true;
                    default:
                        return false;
                }
            }
        );
    }

    /**
     *
     * Returns the path required to navigate from the root to all of the leaf tables
     *
     * @param AggregateMapperInterface $mapper
     *
     * @param array $criteria
     *
     * @return array
     *
     */
    public function getPathFromRoot(AggregateMapperInterface $mapper, array $criteria)
    {
        if ($mapper->getPersistOrder() !== null) {
            return $mapper->getPersistOrder();
        }
        $entry_context = $this->getNode($criteria, $mapper);
        $path[] = $entry_context;
        $path = $this->traverseRelations($mapper, $entry_context, $path);
        $mapper->setPersistOrder($path);
        return $path;
    }

    protected function traverseRelations(AggregateMapperInterface $mapper, \stdClass $node, &$path, &$seen = array())
    {
        $relations = $mapper->lookUpAllRelations($node->relation_name);
        foreach ($relations as $relation) {
            $relation_name = $relation['relation_name'];
            $other_side = $relation['other_side'];
            if (in_array($relation_name, $seen)) {
                continue;
            }
            $seen[] = $relation_name;
            $next_relation = $mapper->lookupRelation($relation_name);
            if ($relation_name === $other_side) {
                $criteria = $this->fromRoot($next_relation, $relation_name, $mapper);
            } else {
                $criteria = $this->fromLeaf($next_relation, $relation_name, $other_side, $mapper);
            }
            $node = $this->getNode($criteria, $mapper);
            $path[] = $node;
            $this->traverseRelations($mapper, $node, $path, $seen);
        }
        return $path;
    }

    protected function fromRoot($next_relation, $relation_name, AggregateMapperInterface $mapper)
    {
        if ($next_relation['owner'] === true) {
            $ref_field = $next_relation['reference_field'];
            $key = $mapper->joinAddress($relation_name,  $mapper->separateMapperFromField($ref_field)->field);
            $value = $mapper->joinAddress(':__root', $next_relation['join_property']);
        } else {
            $key = $mapper->joinAddress($relation_name, $next_relation['join_property']);
            $relation_pieces = explode('.', $relation_name);
            if (count($relation_pieces) === 1) {
                $ref_field = $next_relation['reference_field'];
                $value = $mapper->joinAddress(':__root', $mapper->separateMapperFromField($ref_field)->field);
            } else {
                $value = ':'.$relation_name;
            }
        }
        return array($key => $value);
    }

    protected function fromLeaf($next_relation, $relation_name, $other_side, AggregateMapperInterface $mapper)
    {
        if ($next_relation['owner'] === true) {
            $key = $mapper->joinAddress($other_side, $next_relation['join_property']);
            $value = $mapper->joinAddress(
                ':'.$relation_name,
                $mapper->separateMapperFromField($next_relation['reference_field'])->field
            );
        } else {
            $ref_field = $next_relation['reference_field'];
            $key = $mapper->joinAddress($other_side, $mapper->separateMapperFromField($ref_field)->field);
            $value = $mapper->joinAddress(':'.$relation_name, $next_relation['join_property']);
        }
        return array($key => $value);
    }

    protected function getNode(array $criteria, AggregateMapperInterface $mapper)
    {
        $context = new \stdClass();

        if (empty($criteria)) {
            $context->relation_name = '__root';
            $context->criteria = null;
        } else {
            $property_address = $mapper->separatePropertyFromAddress(key($criteria));
            $context->relation_name = $property_address->address;
            $context->criteria = array($property_address->property => current($criteria));
            $context->fields[] = $property_address->property;
        }
        foreach ($mapper->getRelationToMapper()[$context->relation_name]['relations'] as $relation) {
                $info = $mapper->getRelationMap()[$relation['relation_name']];
            if ($info['owner'] === true && $relation['relation_name'] === $context->relation_name) {
                $field = $mapper->separateMapperFromField($info['reference_field'])->field;
                if (! in_array($field, $context->fields)) {
                    $context->fields[] = $field;
                }
            }
        }
        return $context;
    }
}