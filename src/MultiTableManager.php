<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Query\Select;

/**
 * [summary].
 *
 * [description].
 */
abstract class MultiTableMapper extends AbstractMapper
{
    /**
     *
     * A row data gateway.
     *
     * @var GatewayInterface
     *
     */
    protected $gateways;

    /**
     * $gateways = {
     *     'friendlyName' => $gateWay
     * }
     *
     *
     * @param GatewayInterface $gateway
     *
     * @param array $gateways
     *
     * @param ObjectFactoryInterface $object_factory An object factory.
     *
     * @param FilterInterface $filter A filter for inserts and updates.
     */
    public function __construct(
        GatewayInterface $gateway,
        array $gateways,
        ObjectFactoryInterface $object_factory,
        FilterInterface $filter
    ) {
        $errors = array_filter(
            $gateways,
            function ($value) {
                return ! $value instanceof GatewayInterface;
            }
        );
        if (count($errors) > 0) {
            throw new \InvalidArgumentException('$gateways can only contain instances of GateWayInterface');
        }
        $this->gateway                = $gateway;
        $this->gateways               = $gateways;
        $this->gateways['__root']     = $gateway;
        $this->object_factory         = $object_factory;
        $this->filter                 = $filter;
    }

    public function getIdentityFieldFor($name)
    {
        return $this->gateways[$name]->getprimaryCol();
    }

    public function getIdentityValueFor($name, $object)
    {
        $field = $this->getIdentityFieldFor($name);
        return $object->$field;
    }

    public function setIdentityValueFor($name, $object, $value)
    {
        $field = $this->getIdentityFieldFor($name);
        $object->$field = $value;
    }

    public function fetchObject(Select $select)
    {
        $rows = $this->gateway->fetchAll($select);
        $rows = array_values($this->groupByField($rows, $this->getidentityField()));
        if ($rows) {
            return $this->newObject($rows[0]);
        }
        return false;
    }

    public function fetchCollection(Select $select)
    {
        $field = $this->gateway->getPrimaryCol();
        $rows  = $select->fetchAll();
        return $this->newCollection(
            $this->groupByField($rows, $field)
        );
    }

    protected function groupByField(array $rows, $field)
    {
        $output = array();
        foreach ($rows as $row) {
            $output[$row[$field]][] = $row;
        }
        return $output;
    }

    public function select()
    {
        $selects = array();
        $tables  = $this->parseCols(
            $this->getColsAsFields()
        );
        foreach ($tables as $table => $cols) {
            $selects[] = $this->gateways[$table]->select($cols);
        }
        return $selects;
    }

    protected function parseCols(array $cols)
    {
        $output = array();
        foreach ($cols as $col => $val) {
            $match = explode('.', $col);
            if (count($match) === 1){
                $table = '__root';
                $column = $match[0];
            } else {
                $table  = $match[0];
                $column = $match[1];
            }
            $output[$table] = $column;
        }
        return $output;
    }
}