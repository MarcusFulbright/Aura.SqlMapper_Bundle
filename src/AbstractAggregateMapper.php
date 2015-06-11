<?php
namespace Aura\SqlMapper_Bundle;

/**
 *
 * Describes an Aggregate Domain Object via a series of maps, and provides
 * methods to call factory functions and get results arrangers.
 *
 * @package Aura\SqlMapper_Bundle
 *
 */
abstract class AbstractAggregateMapper implements AggregateMapperInterface
{
    /**
     *
     * The order to persist objects for writing to the DB.
     *
     * @var array An array of relation names.
     *
     */
    public $persist_order = null;

    /**
     *
     * The key to use when referring to the root mapper as a relation.
     *
     * @var string
     *
     */
    protected $root_relation_address = '__root';

    /**
     *
     * The delimiter that separates address segments in property addresses
     * AND mapper addresses.
     *
     * @var string
     *
     */
    protected $address_delimiter = '.';

    /**
     *
     * The cached relation_to_mapper array. It will only be created once on
     * first access. Every other time you retrieve it, it will access this
     * cached version.
     *
     * @var array
     *
     */
    protected $relation_to_mapper;

    /**
     *
     * A factory responsible for creating objects and collections.
     *
     * @var ObjectFactoryInterface
     *
     */
    protected $object_factory;

    /**
     *
     * Constructor
     *
     * @param ObjectFactoryInterface $object_factory The factory for the
     * aggregate domain described here.
     *
     */
    public function __construct(ObjectFactoryInterface $object_factory)
    {
        $this->object_factory = $object_factory;
    }

    /**
     *
     * A map of PropertyAddress => MapperAddress relations
     *
     * @return array
     *
     */
    abstract public function getPropertyMap();

    /**
     *
     * Describes all of the relations as $propertyAddress => $relationInfo[]
     * where $propertyAddress is a string and $relationInfo[] looks like {
     *
     *     $relationInfo['joinProperty'] string The property on
     *     $propertyAddress that represents it's side of the relationship.
     *
     *     $relationInfo['reference'] string Which property (one level up
     *     from $propertyAddress) referenced by 'joinProperty'.
     *
     *     $relationInfo['owner'] bool Whether the relationship can be broken
     *     by updating the value of 'joinProperty' or not.
     *
     *     $relationInfo['type'] string Either 'hasOne' if this is a single
     *     embedded object or 'hasMany' to represent a collection of 0 or more
     *     objects.
     *
     * }
     *
     * @return null|array
     *
     */
    public function getRelationMap()
    {
        return null;
    }

    /**
     *
     * Gets the map that describes EVERY relationship in this object (including
     * root objects)
     *
     * @return array
     *
     */
    public function getRelationToMapper()
    {
        if (isset($this->relation_to_mapper) === false) {
            $this->relation_to_mapper = $this->makeRelationToMapper(
                $this->getPropertyMap(),
                (array) $this->getRelationMap()
            );
        }
        return $this->relation_to_mapper;
    }

    /**
     *
     * Creates the RelationToMapper map from a property map and relation map.
     *
     * @param array $property_map A propertyAddress => mapperAddress map.
     *
     * @param array $relation_map A relationAddress => relationInfo map.
     *
     * @return array The combined RelationToMapper map.
     *
     */
    protected function makeRelationToMapper(array $property_map, array $relation_map)
    {
        $relation_to_mapper = array();
        foreach ($property_map as $property_address => $mapper_address) {
            $property_address = $this->separatePropertyFromAddress($property_address);
            $mapper_address = $this->separateMapperFromField($mapper_address);

            if(! isset($relation_to_mapper[$property_address->address])) {
                $relation_to_mapper[$property_address->address] = array(
                    'mapper' => $mapper_address->mapper,
                    'fields' => array(),
                    'relations' => array()
                );
            }
            $relation_to_mapper[$property_address->address]['fields'][$mapper_address->field] = $property_address->property;
        }

        foreach ($relation_map as $address => &$relation_info) {
            $relatesTo = $this->getRelationByProperty($address);
            $relation_to_mapper[$address]['relations'][] = array(
                'relation_name' => $address,
                'other_side'    => $relatesTo,
                'details'       => &$relation_info
            );
            $relation_to_mapper[$relatesTo]['relations'][] = array(
                'relation_name' => $address,
                'other_side'    => $address,
                'details'       => &$relation_info
            );

            $hidden_field = $this->separateMapperFromField($relation_info['reference_field']);
            if ($relation_info['owner'] === true) {
                $relation_to_mapper[$address]['fields'][$hidden_field->field] = null;
            } else {
                $relation_to_mapper[$relatesTo]['fields'][$hidden_field->field] = null;
            }
        }
        return $relation_to_mapper;
    }

    /**
     *
     * Looks up a relation by name. If there is none, returns null.
     *
     * @param string $relation_name The relation to look up
     *
     * @return array|null The relation info, if it exists.
     *
     */
    public function lookUpRelation($relation_name)
    {
        $relation_map = $this->getRelationMap();
        if (isset($relation_map[$relation_name])) {
            return $relation_map[$relation_name];
        } else {
            return null;
        }
    }

    /**
     *
     * Looks up a relation by name in the relation_to_mapper map. If there is none, returns null.
     *
     * @param string $relation_name The relation to look up
     *
     * @return array|null The relation info, if it exists.
     *
     */
    protected function lookUpRelationToMapper($relation_name, $key = null)
    {
        $relation_name = $relation_name ?: $this->root_relation_address;
        $relation_to_mapper = $this->getRelationToMapper();
        if (isset($relation_to_mapper[$relation_name]) && $key === null) {
            if ($key === null) {
                return $relation_to_mapper[$relation_name];
            }
        }
        if (isset($relation_to_mapper[$relation_name][$key])) {
            return $relation_to_mapper[$relation_name][$key];
        }
        return null;
    }

    /**
     *
     * Looks up a relation by name in the relation_to_mapper map. If there is none, returns null.
     *
     * @param string $relation_name The relation to look up
     *
     * @return array|null The relation info, if it exists.
     *
     */
    public function lookUpAllRelations($relation_name)
    {
        return $this->lookUpRelationToMapper($relation_name, 'relations');
    }

    /**
     *
     * Looks up a relation by name in the relation_to_mapper map. If there is none, returns null.
     *
     * @param string $relation_name The relation to look up
     *
     * @return array|null The relation info, if it exists.
     *
     */
    public function lookUpMapper($relation_name)
    {
        return $this->lookUpRelationToMapper($relation_name, 'mapper');
    }

    /**
     *
     * Looks up a relation by name. If there is none, returns null.
     *
     * @param string $property_address The relation to look up
     *
     * @return \stdClass|null The mapper & field declaration, if it exists
     *
     */
    public function lookUpProperty($property_address)
    {
        $property_map = $this->getPropertyMap();
        if (isset($property_map[$property_address])) {
            return $this->separateMapperFromField($property_map[$property_address]);
        } else {
            return null;
        }
    }

    /**
     *
     * Digests a property address and outputs a standard object that provides
     * the parent address and property in separate properties.
     *
     * @param string $property_address The address to split
     *
     * @param bool $use_root_relation_address Whether or not to use the root relation address instead of '' in single
     * segment property addresses.
     *
     * @return \StdClass A StdObject with an 'address' and 'property' property.
     *
     */
    public function separatePropertyFromAddress($property_address, $use_root_relation_address = true)
    {
        $address_segments = $this->splitStringOnLast(
            $this->address_delimiter,
            $property_address,
            $use_root_relation_address?$this->root_relation_address:''
        );

        $output = new \StdClass();
        $output->address = $address_segments[0];
        $output->property = $address_segments[1];

        return $output;
    }

    /**
     *
     * Digests a mapper address and outputs a standard object that provides the
     * mapper name and field in separate properties.
     *
     * @param string $mapper_address The address to split
     *
     * @return \StdClass A StdObject with a 'mapper' and 'field' property.
     *
     * @throws Exception If $mapper_address does not contain enough segments to
     * describe both a mapper AND a field.
     *
     */
    public function separateMapperFromField($mapper_address)
    {
        if (strpos($mapper_address, $this->address_delimiter) === false) {
            throw new Exception('No mapper declared in mapper address.');
        }

        $address_segments = $this->splitStringOnLast($this->address_delimiter, $mapper_address);

        $output = new \StdClass();
        $output->mapper = $address_segments[0];
        $output->field  = $address_segments[1];

        return $output;
    }

    /**
     *
     * Joins multiple segments by the delimiter.
     *
     * @param ...string|array $pieces Either an array of segments to join by delimiter, or a repeating list of strings.
     *
     * @return string
     *
     */
    public function joinAddress($pieces)
    {
        return implode($this->address_delimiter, is_array($pieces) ? $pieces : func_get_args());
    }

    public function getRelationByProperty($property_address)
    {
        return $this->separatePropertyFromAddress($property_address)->address;
    }

    /**
     *
     * Splits a string on the final occurrence of the provided delimiter.
     *
     * @param string $delimiter The delimiter to explode on.
     *
     * @param string $string The string to split.
     *
     * @param string $prependIfNoDelimiter If the delimiter does not appear
     * in $string, use this as a pseudo first segment.
     *
     * @return array An array where the first member is everything leading up
     * to the last instance of $delimiter, and the second member is everything
     * after.
     *
     */
    protected function splitStringOnLast($delimiter, $string, $prependIfNoDelimiter = '')
    {
        $segments = explode($delimiter, $string);
        $last = array_pop($segments);
        $first = $this->joinAddress($segments);

        if ($first === '') {
            $first = $prependIfNoDelimiter;
        }

        return array($first, $last);
    }

    /**
     *
     * Passes the provided data along to the factory and returns its output.
     *
     * @param array $data The data to pass along to the factory.
     *
     * @return mixed The collection provided by the factory.
     *
     */
    public function newCollection($data)
    {
        return $this->object_factory->newCollection($data);
    }

    /**
     *
     * Passes the provided data along to the factory and returns its output.
     *
     * @param mixed $data The data to pass along to the factory.
     *
     * @return mixed The object instance provided by the factory.
     *
     */
    public function newObject($data)
    {
        return $this->object_factory->newObject($data);
    }

    /**
     *
     * Used to get the Persist Order.
     *
     * @return array|null
     *
     */
    public function getPersistOrder()
    {
        return $this->persist_order;
    }

    /**
     * Used to set the Persist Order.
     *
     * @param array $order
     */
    public function setPersistOrder(array $order)
    {
        $this->persist_order = $order;
    }
}