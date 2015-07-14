<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;

/**
 * Class RowDataArranger
 *
 * Responsible for transforming database output (organized by relation name) into a state that mirrors the
 * aggregate object structure.
 *
 * @package Aura\SqlMapper_Bundle
 */
class EntityArranger implements EntityArrangerInterface
{
    /**
     *
     * Transforms database output into the structure defined by the provided mapper.
     *
     * @param array $data Database output, organized as row domain objects by relation name.
     *
     * @param AggregateMapperInterface $mapper The mapper that describes this dataset.
     *
     * @return array a multi-dimensional array
     *
     */
    public function arrangeRowData(array $data, AggregateMapperInterface $mapper)
    {
        $context = $this->buildContext($data);
        $this->buildOutput($context, $mapper);
        return $this->getInstances($context->output['__root']);
    }

    /**
     *
     * Builds the context object we'll be passing among the methods here.
     *
     * @param array $data The dataset we'll be traversing
     *
     * @return object The context object
     *
     */
    protected function buildContext(array $data)
    {
        $context = (object) array(
            'data'      => $data,
            'output'    => array(),
            'burn_list' => array(),
            'current'   => '__root'
        );

        foreach ($context->data as $address => $rows)
        {
            $context->output[$address] = array();
            foreach ($rows as $row) {
                $context->output[$address][] = (object) array(
                    'row_data' => $row,
                    'instance' => null
                );
            }
        }

        return $context;
    }

    /**
     *
     * Recursively traverses the context and the mapper, building out one branch at a time.
     *
     * @param object $context The context object for this traversal.
     *
     * @param AggregateMapperInterface $mapper The mapper that describes this context.
     *
     * @return object The modified context object.
     *
     */
    protected function buildOutput($context, AggregateMapperInterface $mapper)
    {
        $context->burn_list[] = $context->current;
        $rows = $context->output[$context->current];

        $my_property = $mapper->separatePropertyFromAddress($context->current)->property;
        foreach ($rows as $row) {
            $row->instance = $this->extractPropertiesFromRow($row->row_data, $context->current, $mapper);
            $this->addToUpstreamRelations($row, $my_property, $context, $mapper);
        }

        $this->buildRelations($context, $mapper);

        return $context;
    }

    /**
     *
     * Returns the relation information that links the provided address to its parent.
     *
     * @param string $relation_address The address to check
     *
     * @param AggregateMapperInterface $mapper
     *
     * @return null|array
     *
     */
    protected function getUpstreamRelation($relation_address, AggregateMapperInterface $mapper)
    {
        $relations = $mapper->lookUpAllRelations($relation_address);
        $output = null;
        $upstream_address = $mapper->separatePropertyFromAddress($relation_address)->address;
        foreach ($relations as $relation) {
            if ($relation['other_side'] === $upstream_address) {
                $output = $relation;
            }
        }
        return $output;
    }

    /**
     *
     * Identifies the join fields as 'from' and 'to' properties.
     *
     * @param string $from_address The address joining from.
     *
     * @param array $relation_info The relationship information.
     *
     * @param AggregateMapperInterface $mapper The mapper with field information.
     *
     * @return \stdClass An object with a 'to' and a 'from' property that represents the join fields.
     *
     */
    protected function getJoinFields($from_address, array $relation_info, AggregateMapperInterface $mapper)
    {
        $output = new \stdClass();
        if ($from_address === $relation_info['relation_name'] && !$relation_info['details']['owner']) {
            $my_property = $mapper->joinAddress(
                $from_address,
                $relation_info['details']['join_property']
            );
            $output->from = $mapper->lookupProperty($my_property)->field;
            $output->to = $mapper->separateMapperFromField($relation_info['details']['reference_field'])->field;
        } else {
            $their_property = $mapper->joinAddress(
                $relation_info['other_side'],
                $relation_info['details']['join_property']
            );
            $output->to = $mapper->lookupProperty($their_property)->field;
            $output->from = $mapper->separateMapperFromField($relation_info['details']['reference_field'])->field;
        }
        return $output;
    }

    /**
     *
     * Adds the instance data from the provided row to all appropriate upstream instance data.
     *
     * @param object $row The row domain object.
     *
     * @param string $property_name The name of the property on the upstream instance data to replace.
     *
     * @param object $context The context object.
     *
     * @param AggregateMapperInterface $mapper The mapper that describes this relationship.
     *
     */
    protected function addToUpstreamRelations($row, $property_name, $context, AggregateMapperInterface $mapper)
    {
        $relation_info = $this->getUpstreamRelation($context->current, $mapper);

        if ($relation_info !== null) {
            $join_fields = $this->getJoinFields($context->current, $relation_info, $mapper);
            $my_join_value = $row->row_data->{$join_fields->from};

            $upstream_matches = $this->matchAddress(
                $context->output[$relation_info['other_side']],
                $join_fields->to,
                $my_join_value
            );

            foreach ($upstream_matches as $matchedRow) {
                if ($relation_info['details']['type'] === 'hasOne') {
                    $matchedRow->instance[$property_name] = &$row->instance;
                } else {
                    $matchedRow->instance[$property_name][] = &$row->instance;
                }
            }
        }
    }

    /**
     *
     * Loops through a context, grabs all of the relations by the current address, and then builds output for each one.
     *
     * @param object $context The current context object.
     *
     * @param AggregateMapperInterface $mapper The mapper with the relationship information.
     *
     * @return object The updated context object.
     *
     */
    protected function buildRelations($context, AggregateMapperInterface $mapper)
    {
        $relations = $mapper->lookUpAllRelations($context->current);
        foreach ($relations as $relation) {
            if (!in_array($relation['other_side'], $context->burn_list)) {
                $current = $context->current;
                $context->current = $relation['relation_name'];
                $this->buildOutput($context, $mapper);
                $context->current = $current;
            }
        }
        return $context;
    }

    /**
     *
     * Query an array of rows for a particular value.
     *
     * @param array $dataset The array of rows to query.
     *
     * @param string $field The field to match on.
     *
     * @param mixed $value The value that the field should equal.
     *
     * @return array Resulting matches.
     *
     */
    protected function matchAddress(array $dataset, $field, $value)
    {
        return array_reduce(
            $dataset,
            function($output, $row) use ($field, $value) {
                if ($row->row_data->$field === $value) {
                    $output[] = $row;
                }
                return $output;
            },
            array()
        );
    }

    /**
     *
     * Takes a single row domain and (with the address it represents) creates an output array of instance data with the
     * row domain field values mapped to the appropriate property names.
     *
     * NOTE: This method includes properties that represent relations, but primes their value as either NULL or an empty
     * array, depending on the type of the relation. The assumption as that they will be populated by downstream
     * instance data.
     *
     * @param object $rowdata The row domain object.
     *
     * @param string $current_address The address this row represents.
     *
     * @param AggregateMapperInterface $mapper The mapper that describes that address.
     *
     * @return array The instance data.
     *
     */
    protected function extractPropertiesFromRow($rowdata, $current_address, AggregateMapperInterface $mapper)
    {
        $fields = $mapper->lookUpFields($current_address);
        $output = array();
        foreach (get_object_vars($rowdata) as $field => $value) {
            if($fields[$field]) {
                $output[$fields[$field]] = $value;
            }
        }

        foreach ($mapper->lookUpAllRelations($current_address) as $relation_info) {
            $rel_property_address = $mapper->separatePropertyFromAddress($relation_info['other_side']);
            if ($rel_property_address->address === $current_address) {
                if ($relation_info['details']['type'] === 'hasOne') {
                    $output[$rel_property_address->property] = null;
                } else {
                    $output[$rel_property_address->property] = array();
                }
            }
        }
        return $output;
    }

    /**
     *
     * Reduces an array of rows (with row_data and instance properties) down to just an array of the instance data.
     *
     * @param array $data The array to reduce
     *
     * @return array The reduced array.
     *
     */
    protected function getInstances(array $data) {
        return array_reduce(
            $data,
            function($output, $row) {
                $output[] = &$row->instance;
                return $output;
            },
            array()
        );
    }
}