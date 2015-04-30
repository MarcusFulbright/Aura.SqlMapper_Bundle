<?php
namespace Aura\SqlMapper_Bundle;

/**
 * [summary].
 *
 * [description]
 */
abstract class AbstractManager implements ManagerInterface
{
    /**
     * @var array
     */
    protected $gateways = array();

    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var ObjectFactoryInterface
     */
    protected $object_factory;

    /**
     * @var GatewayInterface
     */
    protected $root_gateway;

    /**
     *
     * @param array $gateways
     *
     * @param MapperInterface $mapper
     *
     * @param ObjectFactoryInterface $object_factory
     *
     */
    public function __construct(
        MapperInterface $mapper,
        ObjectFactoryInterface $object_factory,
        array $gateways = array()
    ) {
        $this->mapper = $mapper;
        $this->object_factory = $object_factory;
        /** @var GatewayInterface $gateway */
        foreach ($gateways as $gateway) {
            $this->gateways[$gateway->getTable()] = $gateway;
        }
        $table_col = $this->mapper->resolveAddress(
            $this->mapper->getIdentityProp()
        );
        $table = $this->getTableAndColumn($table_col)->table;
        $this->root_gateway = $this->gateways[$table];
    }

    /**
     *
     * Returns all join information used for selects.
     *
     * array(
     *     'tableName' => array (
     *         'type'  => 'LEFT',
     *         'table' => 'tableName',
     *         'on'    => 'foo.id = alias.foo_id'
     *     )
     * )
     *
     * @return null
     *
     * @todo handle table alias somehow
     */
    protected function getJoins()
    {
        return null;
    }

    public function select()
    {
        $cols = array();
        /**
         * @todo look into changing the PDO Quoter and using it here instead.
         */
        foreach ($this->mapper->getPropertyMap() as $address => $table_col) {
            $cols[] = $table_col . ' as "' . $address .'"';
        }
        $select =  $this->root_gateway->select($cols);

        /**
         * @todo might have to do some table alias thing here?
         */
        foreach ($this->getJoins() as $table => $info) {
            $select->join(
                $info['type'],
                $info['table'],
                $info['on']
            );
        }
        return $select;
    }

    public function selectBy($address, $val)
    {
        $select = $this->select();
        $where = '"' . $address . '"';
        if (is_array($val)) {
            $where .= " IN (:{$address})";
        } else {
            $where .= " = :{$address}";
        }
        $select->where($where);
        $select->bindValue($address, $val);
        return $select;
    }

    public function insert($object)
    {

    }
}
