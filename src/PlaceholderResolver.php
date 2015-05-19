<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Knows how to resolve criteria for queries.
 *
 * Will either build where, or where IN, clauses appropriately based on the given results. In the case that a
 * criteria is already defined, it just returns what it was given.
 */
class PlaceholderResolver 
{
    /**
     * Traverses results to fill in values for place holders. Can probably get optimized a bit more.
     *
     * @param $value
     * @param array $results
     * @param AggregateMapperInterface $mapper
     * @return array|string
     */
    public function resolve($value, array $results, AggregateMapperInterface $mapper)
    {
        $has_placeholder = substr($value, 0, 1) === ':';
        $where_in        = array();
        if ($has_placeholder) {
            $context = $this->getContext($value);
            foreach ($results[$context->address] as $row_data) {
                $name = $this->translateField($mapper, $context->propIndex);
                $where_in[] = $row_data->$name;
            }
            return $where_in;
        }
        return $value;
    }

    protected function translateField(AggregateMapperInterface $mapper, $prop_index)
    {
        $mapper_address = $mapper->separateMapperFromField(
            $mapper->getPropertyMap()[$prop_index]
        );
        return $mapper_address->field;
    }

    protected function getContext($value)
    {
        $context          = new \stdClass();
        $value_pieces     = explode('.', $value);
        $field            = array_pop($value_pieces);
        $context->address = ltrim(implode('.', $value_pieces), ':');
        if ($context->address === '__root') {
            $context->propIndex = $field;
        } else {
            $context->propIndex = $context->address . '.' . $field;
        }
        return $context;
    }
}