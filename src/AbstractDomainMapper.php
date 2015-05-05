<?php
namespace Aura\SqlMapper_Bundle;
use Aura\SqlMapper_Bundle\Query\Select;

/**
 * [summary].
 *
 * [description].
 *
 */
abstract class AbstractDomainMapper implements DomainMapperInterface
{
    /**
     *
     * @var array
     *
     */
    protected $mappers;

    /**
     * @var MapperInterface
     */
    protected $root_mapper;

    /**
     * @var ObjectFactoryInterface
     */
    protected $factory;

    /**
     * @var FilterInterface
     */
    protected $filter;

    public function __construct(
        MapperInterface $root_mapper,
        array $mappers,
        ObjectFactoryInterface $object_factory,
        FilterInterface $filter
    ) {
        $this->root_mapper = $root_mapper;
        $this->mappers = $mappers;
        $this->factory = $object_factory;
        $this->filter = $filter;
    }

    /**
     *
     * @return array
     *
     */
    abstract public function getPropertyMap();

    /**
     *
     * @return array
     *
     */
    abstract public function getMapperMap();

    /**
     *
     */
    public function getJoins()
    {
        return null;
    }

    public function joinTo(Select $select, array $info, $aliasPrefix = '')
    {
        /** @todo CAN YOU ALWAYS RESOLVE ROOT PROPERTIES TO THEIR FIELD(OR COL) NAME? */
        $mappers = $this->mappers;
        $this->root_mapper->joinTo(
            $select,
            $info,
            $aliasPrefix
        );

        foreach ($this->getJoin() as $propertyName => $subInfo) {
            $mappers[$propertyName]->joinTo(
                $select,
                array(
                    $aliasPrefix . $propertyName . $subInfo['targetProperty'] => $aliasPrefix . $subInfo['rootProperty']
                ),
                $aliasPrefix . $propertyName
            );
        }
        return $select;
    }

    public function select()
    {
        $statements = array();
        $select = $this->root_mapper->select();

        foreach ($this->getJoin() as $propertyName => $info) {
            $this->propertyToMapper($propertyName)->joinTo(
                $select,
                array(
                    $info['targetProperty'] => $info['rootProperty']
                ),
                $propertyName . '.'
            );
        }

        return $statements;
    }

    public function selectBy($vol, $val)
    {

    }

    public function insert($object)
    {

    }

    public function update ($object, $initial_data = null)
    {

    }

    public function delete($object)
    {

    }

    public function fetchObject(Select $select)
    {

    }

    public function newObject(array $row = array())
    {

    }

    public function fetchObjectBy($col, $val)
    {

    }

    public function fetchCollection(Select $select)
    {

    }

    public function newCollection(array $rows = array())
    {

    }

    public function fetchCollectionBy($col, $val)
    {

    }

    /**
     * @throws \Exception
     */
    public function getColsFields()
    {
        throw new \Exception('This method is not implemented in this class');
    }

    public function getIdentityField()
    {
        return $this->root_mapper->getIdentityField();
    }

    public function getIdentityValue($object)
    {
        $field = $this->root_mapper->getIdentityField();
        return $object->$field;
    }

    public function setIdentityValue($object, $value)
    {
        $field = $this->root_mapper->getIdentityField();
        $object->$field = $value;
    }

    public function getWriteConnection()
    {
        return $this->root_mapper->getWriteConnection();
    }
}