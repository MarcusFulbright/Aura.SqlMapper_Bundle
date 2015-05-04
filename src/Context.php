<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Class Context
 * @package Aura\SqlMapper_Bundle
 */
class Context implements ContextInterface
{
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
    ) {
        $this->target = $target;
        $this->pointer = $target;
        $this->mapper = $mapper;
        $this->initial_data = $initial_data;
    }

    /**
     *
     * array(
     *    'object' => $object,
     *    0 => array(
     *       'table' => 'Account',
     *       'data'  => array(
     *          'AccountID' => 1,
     *          'AccountName => 'Xzibit'
     *       ),
     *    ),
     *    1 => array(
     *       'table' => 'Email'
     *       'data' => array(
     *            'col' => 'val'
     *       ),
     *    ),
     *    2 => array(
     *       'table' => false,
     *       'data' => array(
     *         0 => array(
     *              'object' => $object,
     *              0 => array(
     *                 'table' => 'phone',
     *                 'data' => array(
     *                      'PhoneId' => 1,
     *                      'Number' => 55555555
     *              ),
     *              1 => array(
                        'table'  => 'phoneRef',
     *                  'data'   => array(
     *                      'phoneRefID' => 8,
     *                      'phoneRefDescription => 'work'
     *                  )
     *              )
     *         )
     *    )
     * )
     *
     *
     */
    protected function initStack($target, $inital = null)
    {
        $properties = $this->getMappedProperties($target);
        $by_tables  = $this->organizeByTable($properties, $target);
        $by_talbes_initial = $this->organizeByTable($properties, $inital);

    }

    /**
     *
     * Traverse a given object.
     *
     * array (
     *     'table' => array(
     *        col => val
     *     )
     * )
     *
     * @param $properties
     *
     * @return array An array, indexed by table, with an array of rows as values.
     *
     */
    protected function organizeByTable($properties)
    {
        $by_table = array();
        foreach ($properties as $property) {
            $new = $this->handleProperty($property);
            if (! $new) {
                return false;
            }
            $by_table[$new->table] = $new->values;
        }
        return $by_table;
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
    protected function handleProperty(\ReflectionProperty $property)
    {
        $property_address = $this->getAddress($property);

        $val = $property->getValue($object);
        if ($this->mapper->mapsToRelation($property_address)) {

        } else {
            $table = $this->mapper->resolveAddress($property_address);
            $table = $this->mapper->getTableAndColumn($table);
            $output[$table->table][$table->column] = $val;
        }
        return true;
    }

    /**
     * stdClass {
     *    table => 'tableNameYo',
     *    row   => array(col => value),
     *    initial_row => array(col => value),
     * }
     *
     *
     * @return null
     */
    public function getNext()
    {
        /**
         * @todo needs to iterate
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
     * @param \ReflectionProperty $property
     * @return string
     */
    protected function getAddress(\ReflectionProperty $property)
    {
        return $this->address . '.' . $property->name;
    }
}