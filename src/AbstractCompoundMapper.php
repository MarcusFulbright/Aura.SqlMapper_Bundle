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
    abstract public function getColsAsFields();

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
        $cols = $this->getColsAsFields();
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
        $cols = $this->getColsAsFields();
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
        if (! $results) {
            return false;
        }

        return true;
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
        if ($initial_data) {
            return $this->getRowDataChanges($object, $initial_data);
        }
        $context = $this->createTraversalContext($this->getColsAsFields(), $object);
        return $this->traverseAndPersistObject($context);
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
     * For each property in the provided object, organize it by friendly name, and then add it to the
     * output array.
     *
     * @param array $map The map for this context of the object.
     *
     * @param mixed $target The object to traverse.
     *
     * @param array &$data The output array.
     *
     * @return array An array, indexed by friendly name, with an array of rows as values.\
     *
     * @todo  separate some of these responsibilities, because this is a monster.
     *
     */
    
    /**
     * RESPONSIBILITIES
     * 1) Gather Properties
     * 2) Object Property Iteration
     * 3) Group Values by Friendly Name
     * 4) Determine what needs to be persisted
     * 5) Update object if necessary
     */
    protected function traverseAndPersistObject(stdClass $context, $persist = false)
    {
        $by_friendly_name = array();

        /** @var \ReflectionProperty $property */
        foreach ($context->properties as $property) {
            $col = $context->map[$property->name];
            $value = $property->getValue($context->target);
            $friendly_name = $this->getFriendlyName($col);

            if ($friendly_name === false) {

                $arrayContext = $this->createTraversalContext($col, $val, $data);
                if ($this->traverseAndPersistArray($arrayContext) === false) {
                    return false;
                }

            } else {
                $by_friendly_name[$friendly_name][$col] = $value;
            }
        }

        if ($persist === true) {
            foreach ($by_friendly_name as $friendlyName => $row) {
                $result = $this->gateway->persist($row);
                $col_to_property_map = $this->safeArrayFlip($context->map);

                if ($result === false) {
                    return false;
                }

                $property_name = $col_to_property_map[$result['col']];
                $context->properties[$property_name]->setValue($context->target, $result['val']);
            }
        } else {
            // DELETE
        }
        return true;
    }

    /**
     *
     * Creates the context object we'll need to pass between traversal methods.
     *
     * @param array $map The map representing this stage of traversal.
     *
     * @param mixed $target The object that the map corresponds to.
     *
     * @param array &$output The cumulative data array.
     *
     * @return stdClass The context object.
     *
     * @todo Consider adding the by_friendly_name array to this Class.
     *
     */
    protected function createTraversalContext(array $map, $target, array &$output = array())
    {
        $context = new stdClass();
        $context->target = $target;
        $context->properties = $this->getMappedProperties($target);
        $context->output = &$output;
        return $context;
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
    protected function safeArrayFlip(array $array) {
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
     * For each member of an array, traverse as an object.
     * 
     * @param array $map The propertyname -> fieldname map for these objects.
     *
     * @param array $target The array to traverse.
     *
     * @param array &$data The output dataset.
     *
     * @return array An array, indexed by friendly name, with an array of rows as values.
     *
     */
    protected function traverseAndPersistArray(array $map, array $target, array &$data)
    {
        foreach ($target as $member) {
            if ($this->traverseAndPersistObject($map, $member, $data) === false) {
                return false;
            }
        }
        return true;
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
