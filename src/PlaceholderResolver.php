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
     * @todo beef up tests so that they include returning the original value if no place holder present
     *
     * @param array $criteria a set of key values to resolve against
     *
     * @param array $data The data-set to resolve place holders against. Rows can be raw array or stdClass
     *
     * @param AggregateMapperInterface $mapper
     *
     * @return array|string the resolved values, or the original value if there was nothing to resolve
     *
     */
    public function resolveCriteria(array $criteria, array $data, AggregateMapperInterface $mapper)
    {
        foreach ($criteria as $field => $value) {
            $value = is_array($value) ? $value : [$value];
            foreach ($value as $address) {
                $has_placeholder = substr($address, 0, 1) === ':';
                $where_in = [];
                if ($has_placeholder) {
                    $prop_address = $mapper->separatePropertyFromAddress(ltrim($address, ':'));
                    foreach ($data[$prop_address->address] as $row) {
                        if ($row instanceof \stdClass) {
                            $where_in[] = $row->{$prop_address->property};
                        } else {
                            $where_in[] = $row[$prop_address->property];
                        }
                    }
                    $criteria[$field] = $where_in;
                } else {
                    $criteria[$field] = $value;
                }
            }
        }
        return $criteria;
    }

    public function resolveRowData(\stdClass $row_data, array $data)
    {
        $props = get_object_vars($row_data);
        foreach ($props as $prop => $value) {
            $has_placeholder = substr($value, 0, 1) === ':';
            if ($has_placeholder) {
                $keys = ['relation', 'mapper', 'index', 'field'];
                $info = array_combine($keys, explode(':', ltrim($value, ':')));
                $row_data->$prop = $data[$info['relation']][$info['index']]->row_data->$info['field'];
            }
        }
        return $row_data;
    }
}