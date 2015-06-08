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
     *
     * If necessary,traverses values for place holders and resolves them, otherwise just returns the value.
     *
     * @todo Need to teach this to check each value in an array and resolve place holders that are found
     *
     * @param mixed $value could potentially have a placeholder
     *
     * @param array $data The data-set to resolve place holders against. Rows can be raw array or stdClass
     *
     * @param AggregateMapperInterface $mapper
     *
     * @return array|string the resolved values, or the original value if there was nothing to resolve
     *
     */
    public function resolve($value, array $data, AggregateMapperInterface $mapper)
    {
        if (is_array($value)) {
            return $value;
        }
        $has_placeholder = substr($value, 0, 1) === ':';
        $where_in        = array();
        if ($has_placeholder) {
            $prop_address = $mapper->separatePropertyFromAddress(ltrim($value, ':'));
            foreach ($data[$prop_address->address] as $row) {
                if ($row instanceof \stdClass) {
                    $where_in[] = $row->{$prop_address->property};
                } else{
                    $where_in[] = $row[$prop_address->property];
                }
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
}