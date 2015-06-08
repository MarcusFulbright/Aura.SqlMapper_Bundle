<?php
namespace Aura\SqlMapper_Bundle;

use \ReflectionClass;

class RowDataExtractor {

    /**
     *
     * Turns an Aggregate Domain Object into a series of row-data arrays, organized by relation address.
     *
     * @param mixed $objects The AggregateDomainObject to cycle through.
     *
     * @param AggregateMapperInterface $mapper The associated mapper.
     *
     * @return array The row data organized by relation address.
     *
     * @throws \Exception If provided mapper doesn't have a cached persist_order
     *
     */
    public function getRowData($objects, AggregateMapperInterface $mapper)
    {
        if (is_array($objects) === false) {
            $objects = array($objects);
        }

        $output = $this->getInitialOutputArray($mapper);
        foreach ($objects as $object) {
            $this->iterateOverProperties($object, $mapper, '', $output);
        }
        return $output;
    }

    /**
     *
     * Uses the persist order cached on the mapper to pre-build the output array (and therefore preserve the persist
     * order, if supplied).
     *
     * @param AggregateMapperInterface $mapper The mapper we want to build for.
     *
     * @return array The output array.
     *
     */
    protected function getInitialOutputArray(AggregateMapperInterface $mapper)
    {
        $output = array();
        if (property_exists($mapper, 'persist_order')) {
            $relation_to_mapper = $mapper->getRelationToMapper();
            $persist_order = $mapper->getPersistOrder();
            foreach ($persist_order as $relation_address) {
                $output [$relation_address] = array($relation_to_mapper[$relation_address]['mapper'] => array());
            }
        }
        return $output;
    }

    /**
     *
     * Operates on a single level of the aggregate domain object and extracts any relevant row data.
     *
     * @param mixed $object An object (or a section of an object) from which we extract our data.
     *
     * @param AggregateMapperInterface $mapper The aggregate mapper that describes this object.
     *
     * @param string $current_address The property address of this object section.
     *
     * @param array $output The array to append the output.
     *
     * @return array
     *
     */
    protected function iterateOverProperties($object, AggregateMapperInterface $mapper, $current_address, array &$output)
    {
        $mapped = $this->getMappedProperties($object, $mapper, $current_address);

        $current_relation = $current_address ?: "__root";
        $output[$current_relation][$mapped->base_mapper][] = &$mapped->base_fields;

        $all_relations = $mapper->lookUpAllRelations($current_relation);
        foreach ($all_relations as $relation_info) {
            if ($this->needsPlaceholder($current_address, $relation_info['relation_name'], $mapper)) {
                $placeholder = $this->getPlaceholder($relation_info['relation_name'], $mapper, $output);
                $mapped->base_fields[$placeholder->field] = $placeholder->value;
            }
        }

        foreach ($mapped->relations as $relation_address => $relation) {
            if ($mapper->lookUpRelation($relation_address)['type'] === 'hasOne') {
                $relation = array($relation);
            }

            foreach ($relation as $next_object) {
                $this->iterateOverProperties($next_object, $mapper, $relation_address, $output);
            }
        }

        return $output;
    }

    /**
     *
     * Determines whether the provided $from_address needs a placeholder from the provided $relation_name.
     *
     * @param string $address The address where we would put the placeholder field.
     *
     * @param string $relation_name The name of the relationship to check.
     *
     * @param AggregateMapperInterface $mapper The aggregate mapper that describes that relation.
     *
     * @return bool Whether or not there should be a placeholder at $address.
     *
     */
    protected function needsPlaceholder($address, $relation_name, AggregateMapperInterface $mapper) {
        $relation = $mapper->lookUpRelation($relation_name);
        $defined_by_me = $address === $relation_name;

        $relation_parent = $mapper->separatePropertyFromAddress($relation_name, false)->address;
        $in_relationship = $defined_by_me || $relation_parent === $address;

        return ($relation['owner'] === $defined_by_me) && $in_relationship;
    }

    /**
     *
     * Retrieves the appropriate placeholder for the provided relationship.
     *
     * @param string $relation_name The relation we need a placeholder for.
     *
     * @param AggregateMapperInterface $mapper The mapper with information on this relation.
     *
     * @param array $output The context for the placeholder.
     *
     * @return object An object with field property and a placeholder property.
     *
     */
    protected function getPlaceholder($relation_name, AggregateMapperInterface $mapper, array $output) {
        $relation = $mapper->lookUpRelation($relation_name);
        $ref_field = $mapper->separateMapperFromField($relation['reference_field'])->field;

        if (!$relation['owner']) {
            $join_address = $relation_name;
        } else {
            $join_address = $mapper->separatePropertyFromAddress($relation_name)->address;
        }

        if ($join_address === '__root') {
            $join_property_address = $relation['join_property'];
        } else {
            $join_property_address = $this->joinAddressAndProperty($join_address, $relation['join_property'], $mapper);
        }

        $join_mapper_field = $mapper->lookUpProperty($join_property_address);
        $join_mapper = $join_mapper_field->mapper;
        $join_field  = $join_mapper_field->field;

        $index = $this->getIndexForPlaceholder($join_address, $join_mapper, $relation['owner'], $output);

        return (object) array(
            'field' => $ref_field,
            'value' => ":{$join_address}:{$join_mapper}:{$index}:{$join_field}"
        );
    }

    /**
     *
     * Uses the provided output array to get an index for
     *
     * @param string $join_address
     *
     * @param string $join_mapper_name
     *
     * @param bool $row_has_been_created Whether or not the row we're associating with has been created yet or not.
     *
     * @param array $output The context for our index (The cumulative output array).
     *
     * @return int The placeholder index.
     *
     */
    protected function getIndexForPlaceholder($join_address, $join_mapper_name, $row_has_been_created, array $output)
    {
        $index = 0;
        if (isset($output[$join_address])) {
            $index = count($output[$join_address][$join_mapper_name]) - ($row_has_been_created ? 1 : 0);
        }
        return $index;
    }


    /**
     *
     * Returns an array of properties on an object as $property=>$value. This is meant to allow normalization of reads
     * on associative arrays, stdObjects, and custom classes.
     *
     * @param array|\stdClass|mixed $object The object to extract a property list from.
     *
     * @return array The $property=>$value array.
     *
     */
    protected function getProperties($object) {
        if (is_array($object)) {
            return $object;
        }

        if ($object instanceof \stdClass) {
            return get_object_vars ($object);
        }

        $reflection = new \ReflectionClass($object);
        $output = array();
        array_walk(
            $reflection->getProperties(),
            function($property) use(&$output) {
                $property->setAccessible(true);
                $output[$property->name] = $property->getValue($object);
            }
        );
        return $output;
    }

    /**
     *
     * For a given object, extract properties mapped to fields or relations, and extract the mapper associated with this
     * address of the object.
     *
     * @param mixed $object The object to extract from.
     *
     * @param AggregateMapperInterface $mapper The aggregate mapper that describes this object.
     *
     * @param string $current_address The address that we pruned $object from.
     *
     * @return \stdClass
     *
     */
    protected function getMappedProperties($object, AggregateMapperInterface $mapper, $current_address) {
        $output = new \stdClass();
        $output->base_fields = array();
        $output->relations = array();

        $property_map = $mapper->getPropertyMap();
        $relation_map = $mapper->getRelationMap();

        foreach ($this->getProperties($object) as $property => $value) {
            $property_address = $this->joinAddressAndProperty($current_address, $property, $mapper);
            if (isset($property_map[$property_address])) {
                $mapper_address = $property_map[$property_address];
                $mapper_field = $mapper->separateMapperFromField($mapper_address);
                $output->base_mapper = $mapper_field->mapper;
                $output->base_fields[$mapper_field->field] = $value;
            } elseif (isset($relation_map[$property_address])) {
                $output->relations[$property_address] = $value;
            }
        }
        return $output;
    }

    /**
     *
     * Utility method that pieces together two parts of an address.
     *
     * @param string $address A part of the output address.
     *
     * @param string $property A second part of the output address.
     *
     * @return string
     *
     */
    protected function joinAddressAndProperty($address, $property, AggregateMapperInterface $mapper)
    {
        if ($address === '') {
            return $property;
        } else {
            return $mapper->joinAddress($address, $property);
        }
    }
}