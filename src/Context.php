<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\MapperInterface;

/**
 * Class Context
 * @package Aura\SqlMapper_Bundle
 */
class Context {

    protected $target;
    protected $initial_data;

    protected $mapper;

    protected $pointer;
    protected $address = '';
    protected $current = null;

    /**
     * @param object $target
     * @param \Aura\SqlMapper_Bundle\MapperInterface $mapper
     * @param object $initial_data
     */
    public function __construct(
        $target,
        MapperInterface $mapper,
        $initial_data = null
    )
    {
        $this->target = $target;
        $this->pointer = $target;
        $this->mapper = $mapper;
        $this->initial_data = $initial_data;
    }

    public function getNext()
    {
        /**
         * @todo
         */
        return $this->getCurrent();
    }

    protected function getFirst()
    {

    }

    public function getCurrent()
    {
        return $this->current;
    }

    /**
     *
     * Returns an array of properties that exist in the current context of the provided map.
     *
     * @param mixed $target The object we care about.
     *
     * @return array An array of ReflectionProperties keyed by address.
     *
     */
    protected function getMappedProperties($target)
    {
        if (is_array($target)) {
            $target = (object) $target;
        }
        $properties = $this->getPropertiesArray($target);
        $map = $this->mapper->getPropertyMap();
        $output = array();
        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if (isset($map[$this->address . '.' . $property->name])) {
                $output[$this->address . '.' .  $property->name] = $property;
            }
        }
        return $output;
    }

    /**
     * @param $target
     * @return \ReflectionProperty[]
     */
    protected function getPropertiesArray($target)
    {
        $target_reflection = new \ReflectionClass($target);
        $target_properties = $target_reflection->getProperties();
        return $target_properties;
    }

    /**
     *
     * Traverse a given object.
     *
     * @param $properties
     *
     * @return array An array, indexed by friendly name, with an array of rows as values.\
     *
     */
    protected function organizeByTable($properties)
    {
        $by_friendly_name = array();
        foreach ($properties as $property) {
            if (! $this->handleProperty($property, $by_friendly_name)) {
                return false;
            }
        }
        return $by_friendly_name;
    }

    /**
     *
     * For a given property (and it's traversal context), by friendly name and add to the output array.
     *
     * If we come across a nested array in the map, then traverse that array.
     *
     * @param \ReflectionProperty $property The property we want to add to the output array.
     *
     * @param  array $output The cumulative output array, indexed by friendly name.
     *
     * @return bool Whether or not we were successful in handling this property.
     *
     */
    protected function handleProperty(\ReflectionProperty $property, array &$output = array())
    {
        $propertyAddress = $this->getAddress($property);
        $val = $property->getValue($property);
        if ($this->mapper->mapsToRelation($propertyAddress)) {
            if ($this->traverseArray($val, $col, $context->callback) === false) {
                return false;
            }
        } else {
            $output[$friendly_name][$col] = $val;
        }
        return true;
    }

    /**
     * @param \ReflectionProperty $property
     * @return string
     */
    protected function getAddress(\ReflectionProperty $property)
    {
        return $this->address . '.' . $property->name;
    }
}