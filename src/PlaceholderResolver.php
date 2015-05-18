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
     * @return array|mixed|string
     */
    public function resolve($value, array $results, AggregateMapperInterface $mapper)
    {
        $has_placeholder = substr($value, 0, 1) === ':';
        $where_in        = array();
        if ($has_placeholder) {
            $value_pieces = explode('.', $value);
            $field        = array_pop($value_pieces);
            $address      = ltrim(implode('.', $value_pieces), ':');
            foreach ($results[$address] as $row_data) {
                if ($field === 'floorID') {
                    $property = $mapper->getPropertyMap()[$field];
                    $mapper_address = $mapper->separateMapperFromField($property);
                    $name = $mapper_address->field;
                    $where_in[] = $row_data->$name;
                } else {
                    $value = ltrim($value, ':');
                    /*
                     * if AggregateBuilder had __root prefix in the propertyMap this could be avoided
                     */
                    if ($value === '__root.id') {
                        $value = preg_replace("/(^.+[.])/", '', $value);
                    }
                    $mapper_address = $mapper->separateMapperFromField(
                        $mapper->getPropertyMap()[$value]
                    );
                    $name = $mapper_address->field;
                    $where_in[] = $row_data->$name;
                }
            }
            return $where_in;
        }
        return $value;
    }
}