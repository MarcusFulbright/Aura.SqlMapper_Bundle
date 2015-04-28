<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Query\Select;

/**
 * [summary].
 *
 * [description].
 */
abstract class AbstractCompoundMapper implements CompoundMapperInterface
{
        /**
     *
     * A callable to create individual objects.
     *
     * @var callable
     *
     */
    protected $object_factory;

    /**
     *
     * A callable to create object collections.
     *
     * @var callable
     *
     */
    protected $collection_factory;

    /**
     *
     * A filter for inserts and updates.
     *
     * @var FilterInterface
     *
     */
    protected $filter;

    /**
     *
     * A row data gateway.
     *
     * @var GatewayInterface
     *
     */
    protected $gateway;

    /**
     *
     * Constructor.
     *
     * @param CompoundGatewayInterface $gateway A row data gateway.
     *
     * @param ObjectFactoryInterface $object_factory An object factory.
     *
     * @param FilterInterface $filter A filter for inserts and updates.
     *
     */
    public function __construct(
        CompoundGatewayInterface $gateway,
        ObjectFactoryInterface $object_factory,
        FilterInterface $filter
    ) {
        $this->gateway = $gateway;
        $this->object_factory = $object_factory;
        $this->filter = $filter;
    }

    /**
     * array(
     *    'propertyName' => 'friendlyName.Column',
     *    'propertyName' => array(
     *         'propertyName' => 'friendlyName.Column'
     *     )
     * );
     *
     * @return mixed
     */
    abstract public function getColsFields();

    /**
     *
     * Instantiates a new individual object from an array of field data.
     *
     * @param array $row Row data for the individual object.
     *
     * @return mixed
     *
     */
    public function newObject(array $row = array())
    {
        return $this->object_factory->newObject($row);
    }

    /**
     *
     * Instantiates a new collection from an array of row data arrays.
     *
     * @param array $rows An array of row data arrays.
     *
     * @return mixed
     *
     */
    public function newCollection(array $rows = array())
    {
        return $this->object_factory->newCollection($rows);
    }

    public function fetchObject(Select $select)
    {
        $row = $this->gateway->fetchRow($select);

        if ($row) {
            return $this->newObject($row);
        }
        return false;
    }

    /**
     *
     * Returns an individual object from the gateway, for a given column and
     * value.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value to match against; this can be an array
     * of values.
     *
     * @return object|false
     *
     */
    public function fetchObjectBy($col, $val)
    {
        $select = $this->selectBy($col, $val);
        return $this->fetchObject($select);
    }

   /**
     *
     * Returns a collection from the gateway using a Select.
     *
     * @param Select $select Select statement for the collection.
     *
     * @return object|array
     *
     */
    public function fetchCollection(Select $select)
    {
        $rows = $this->gateway->fetchRows($select);
        if ($rows) {
            return $this->newCollection($rows);
        }
        return array();
    }

    /**
     *
     * Returns a collection from the gateway, for a given column and value(s).
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value to match against; this can be an array
     * of values.
     *
     * @return object|array
     *
     */
    public function fetchCollectionBy($col, $val)
    {
        $select = $this->selectBy($col, $val);
        return $this->fetchCollection($select);
    }

    /**
     *
     * Returns an array of collections from the gateway using a Select;
     * the array is keyed on the values of a specified object field.
     *
     * @param Select $select Select statement for the collections.
     *
     * @param mixed $field Key the array on the values of this object field.
     *
     * @return array
     *
     */
    public function fetchCollections(Select $select, $field)
    {
        $rows = $this->gateway->fetchRows($select);

        $row_sets = array();
        foreach ($rows as $row) {
            $key = $row[$field];
            $row_sets[$key][] = $row;
        }

        $collections = array();
        foreach ($row_sets as $key => $row_set) {
            $collections[$key] = $this->newCollection($row_set);
        }

        return $collections;
    }

    /**
     *
     * Returns an array of collections from the gateway, for a given column and
     * value(s); the array is keyed on the values of a specified object field.
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value to match against; this can be an array
     * of values.
     *
     * @param mixed $field Key the array on the values of this object field.
     *
     * @return object|false
     *
     */
    public function fetchCollectionsBy($col, $val, $field = null)
    {
        $select = $this->selectBy($col, $val);
        return $this->fetchCollections($select, $field);
    }

    /**
     *
     * Returns a new Select query from the gateway, with field names mapped
     * as aliases on the underlying column names.
     *
     * @return Select
     *
     */
    public function select()
    {
        $cols = $this->getColsFields();
        return $this->gateway->select($cols);
    }

    /**
     *
     * Returns a new Select query from the gateway, with field names mapped
     * as aliases on the underlying column names, for a given column and
     * value(s).
     *
     * @param string $col The column to use for matching.
     *
     * @param mixed $val The value(s) to match against; this can be an array
     * of values.
     *
     * @return Select
     *
     */
    public function selectBy($col, $val)
    {
        $cols = $this->getColsFields();
        return $this->gateway->selectBy($col, $val, $cols);
    }

    /**
     *
     * Inserts an individual object through the gateway.
     *
     * @param object $object The individual object to insert.
     *
     * @return bool
     *
     */
    public function insert($object)
    {
        $this->filter->forInsert($object);
        $results = $this->persistObjectData($object);
        return (bool) $results;
    }

    /**
     *
     * Inserts an individual object through the gateway.
     *
     * @param object $object The individual object to insert.
     *
     * @return bool
     *
     */
    public function update($object, $initial_data = null)
    {
        $this->filter->forUpdate($object);
        $results = $this->persistObjectData($object, $initial_data);
        return (bool) $results;
    }

    /**
     *
     * Returns row data ready for the gateway to use.
     *
     * @param mixed $object The object that represents the current state
     *
     * @param mixed $initial_data Represents the previous state of $object
     *
     * @return array An array, indexed by friendly name, with an array of rows as values.
     *
     */
    protected function persistObjectData($object, $initial_data = null)
    {
        $context = $this->createTraversalContext($this->getColsFields(), $object);
        return $this->traverseObject($context, array($this, 'persist'));
    }

    /**
     *
     * Given a row and a context, persist that row in the Gateway.
     *
     * On success, this will update the primary field on $context->target.
     *
     * @param array $row The row to persist.
     *
     * @param string $friendly_name The friendly name of the gateway.
     *
     * @param stdClass $context The context of the current traversal operation.
     *
     * @return boolean Whether or not the persist operation was successful.
     *
     */
    protected function persist($row, $friendly_name, stdClass $context)
    {
        $result = $this->gateway->persist($row);
        if ($result) {
            $property_name = $context->inverseMap[$result['primary_column']];
            $context->properties[$property_name]->setValue($context->target, $result['primary_value']);
        }
        return (bool) $result;
    }

    /**
     *
     * Inserts an individual object through the gateway.
     *
     * @param object $object The individual object to delete.
     *
     * @return bool
     *
     */
    public function delete($object)
    {
        $context = $this->createTraversalContext($this->getColsFields(), $object);
        return (bool) $this->traverseObject($context, array($this->gateway, 'delete'));
    }

    /**
     *
     * Creates the Traversal Context object we'll need to pass between traversal methods.
     *
     * This object outputs this {
     *
     *     @property $context->target object The object we're concerned with traversing.
     *
     *     @property $context->properties array An array of \ReflectionProperties.
     *
     *     @property $context->map array The property-to-column map.
     *
     *     @property $context->inverseMap array The column-to-property map.
     *
     *     @property $context->callback \callable A function to call on individual rows.
     * }
     *
     * @param array $map The map representing this stage of traversal.
     *
     * @param mixed $target The object that the map corresponds to.
     *
     * @param array &$output The cumulative data array.
     *
     * @return stdClass The context object.
     *
     */
    protected function createTraversalContext(array $map, $target, callable $callback, array &$output = array())
    {
        $context = new stdClass();
        $context->target = $target;
        $context->properties = $this->getMappedProperties($target);
        $context->callback = $callback;
        $context->map = $map;
        $context->inverseMap = $this->safeArrayFlip($context->map);
        return $context;
    }

    /**
     *
     * Returns an array of properties that exist in the current context of the provided map.
     *
     * @param mixed $object The object we care about.
     *
     * @param array $map The PropertyName->FieldName map of the current context.
     *
     * @return array An array of ReflectionProperties.
     *
     */
    protected function getMappedProperties($object, $map)
    {
        if (is_array($target)) {
            $target = (object) $target;
        }
        $reflection = new \ReflectionClass($target);
        $properties = $reflection->getProperties();

        $output = array();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if (isset($map[$property->name])) {
                $output[$property->name] = $property;
            }
        }
        return $output;
    }

    /**
     *
     * Traverse a given context.
     *
     * @param array $map The map for this context of the object.
     *
     * @param mixed $target The object to traverse.
     *
     * @param array &$data The output array.
     *
     * @return array An array, indexed by friendly name, with an array of rows as values.\
     *
     */
    protected function traverseObject(stdClass $context)
    {
        $by_friendly_name = array();

        /** @var \ReflectionProperty $property */
        foreach ($context->properties as $property) {
            if (! $this->handleProperty($property, $context, $by_friendly_name)) {
                return false;
            }
        }

        return $this->walkTraversed($by_friendly_name, $context);
    }

    /**
     *
     * For a given property (and it's traversal context), by friendly name and add to the output array.
     *
     * If we come across a nested array in the map, then traverse that array.
     *
     * @param \ReflectionProperty $property The property we want to add to the output array.
     *
     * @param stdClass $context The Traversal Context object.
     *
     * @param  array $output The cumulative output array, indexed by friendly name.
     *
     * @return bool Whether or not we were successful in handling this property.
     *
     */
    protected function handleProperty(\ReflectionProperty $property, \stdClass $context, array &$output = array())
    {
        $col = $context->map[$property->name];
        $friendly_name = $this->getFriendlyName($col);
        if ($friendly_name === false) {
            if ($this->traverseArray($val, $col, $context->callback) === false) {
                return false;
            }
        } else {
            $value = $property->getValue($context->target);
            $output[$friendly_name][$col] = $value;
        }
        return true;
    }

    /**
     *
     * For each member of an array, traverse as an object. This method will stop
     * processing members at the first failure.
     *
     * @param array $map The propertyname -> fieldname map for these objects.
     *
     * @param array $array The array to traverse.
     *
     * @param array &$callback The current callback method.
     *
     * @return bool Whether all of these members were processed successfuly.
     *
     */
    protected function traverseArray(array $map, array $array, callable $callback)
    {
        foreach ($context->target as $member) {
            $context = $this->createTraversalContext($map, $array, $callback);
            if ($this->traverseObject($context) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * Walks the output of handleProperty (the by_friendly_name array) and
     * executes the callback for each row extracted. This method will stop
     * processing rows at the first failure.
     *
     * @param array $by_friendly_name An array of rows, indexed by friendly name.
     *
     * @param stdClass $context The traversal context object for the object these rows came from.
     *
     * @return bool Whether this was
     *
     */
    protected function walkTraversed(array $by_friendly_name, $context)
    {
        $success = true;
        array_walk(
            $by_friendly_name,
            function($row, $friendly_name, $context) use (&$success) {
                if ($success === true) {
                    $success = $context->callback($row, $friendly_name, $context);
                }
            },
            $context
        );
        return $success;
    }

    /**
     *
     * Returns an array where values are keys and keys are values. Removes any member that cannot be used this way.
     *
     * @param array $array The array to flip.
     *
     * @return array The flipped array.
     *
     */
    protected function safeArrayFlip(array $array)
    {
        $output = array();
        foreach ($array as $key => $value) {
            if (is_string($value) || is_int($value)) {
                $output[$value] = $key;
            }
        }
        return $output;
    }

    /**
     *
     * Merges a data array split by friendly name into the output array.
     *
     * @param array $context Represents data for this context indexed by friendlyname.
     *
     * @param array &$data An array, indexed by friendly name, with an array of rows as values.
     *
     */
    protected function addToDataArray(array $context, array &$data)
    {
        foreach ($context as $friendly_name => $properties) {
            $data[$friendly_name][] = $properties;
        }
        return $data;
    }

    /**
     *
     * Returns a declared friendlyname (or '__root' if none is declare) for any given column name. If the
     * column is not parseable, return false.
     *
     * @param mixed $column The column to check for friendly name.
     *
     * @return mixed The parsed friendlyname or false if not parseable.
     *
     */
    protected function getFriendlyName($column)
    {
        if (! is_string($column)) {
            return false;
        }

        $pieces = explode('.', $column);
        if (count($pieces) > 1) {
            return $pieces[0];
        }
        return '__root';
    }
}
