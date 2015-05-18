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
    public function resolve($value, array $results)
    {
        $has_placeholder = substr($value, 0, 1) === ':';
        $where_in        = array();
        if ($has_placeholder) {
            $value_pieces = explode('.', $value);
            $field        = array_pop($value_pieces);
            $address      = ltrim(implode('.', $value_pieces), ':');
            foreach ($results[$address] as $row_data) {
                $where_in[] = $row_data->$field;
            }
            return $where_in;
        }
        return $value;
    }
}