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
    abstract function getColsAsFields();

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

        $data = $this->getRowData($object);
        $row = $this->gateway->insert($data);
        if (! $row) {
            return false;
        }

        if ($this->gateway->isAutoPrimary()) {
            $this->setIdentityValue(
                $object,
                $this->gateway->getPrimaryVal($row)
            );
        }

        return true;
    }
    protected function getRowData($object, $initial_data = null)
    {
        if ($initial_data) {
            return $this->getRowDataChanges($object, $initial_data);
        }
        $this->traverseColsMap($this->getColsAsFields(), $object, $data);
    }


    /**
     * array(
     *    'propertyName' => 'friendlyName.Column',
     *    'propertyName' => array(
     *         'propertyName' => 'friendlyName.Column'
     *     )
     * );
     *
     * array(
     *    'friendlyName.column' => 'val',
     *    'friendlyName' => array(
     *       0 => array(
     *            'friendlyName.column' => 'val'
     *       )
     *    )
     * )
     */
    protected function traverseColsMap(array $map, $target, array &$data)
    {
        if (is_array($target)) {
            $target = (object) $target;
        }
        $reflection = new \ReflectionClass($target);
        $properties = $reflection->getProperties();

        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $col = $map[$property->getName()];
            $value = $property->getValue($target);
            if (is_array($col)) {


                $this->traverseColsMap($col, $value, $data);
            } else {
                $data[$col] = $value;
            }
        }
        return $data;
    }
}