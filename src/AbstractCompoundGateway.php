<?php
namespace Aura\SqlMapper_Bundle;
use Aura\Sql\ConnectionLocator;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;

/**
 * [summary].
 *
 * [description]
 */
abstract class AbstractCompoundGateway implements CompoundGatewayInterface
{
    protected $rootGateway;

    protected $leafGateways;

    protected $allGateways;

        /**
     *
     * A database connection locator.
     *
     * @var ConnectionLocator
     *
     */
    protected $connection_locator;

    /**
     *
     * A factory to create query statements.
     *
     * @var QueryFactory
     *
     */
    protected $query_factory;

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
     * A read connection drawn from the connection locator.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $read_connection;

    /**
     *
     * A write connection drawn from the connection locator.
     *
     * @var ExtendedPdoInterface
     *
     */
    protected $write_connection;

    /**
     *
     * Constructor.
     *
     * @param GatewayInterface $rootGateway
     *
     * @param array $leafGateways
     *
     * @param ConnectionLocator $connection_locator A connection locator.
     *
     * @param ConnectedQueryFactory $query_factory A query factory.
     *
     * @param FilterInterface $filter A filter for inserts and updates.
     */
    public function __construct(
        GatewayInterface $rootGateway,
        array $leafGateways,
        ConnectionLocator $connection_locator,
        ConnectedQueryFactory $query_factory,
        FilterInterface $filter
    ) {
       // validate $leafGateway
        $this->rootGateway = $rootGateway;
        $this->leafGateways = $leafGateways;
        $this->connection_locator = $connection_locator;
        $this->query_factory = $query_factory;
        $this->filter = $filter;
        $this->allGateways = array_merge(
            array('__root' => $this->rootGateway),
            $this->leafGateways
        );

    }

    /**
     * @return string
     */
    public function getRootTable()
    {
        return $this->rootGateway->getTable();
    }


    /**
     * @return string
     */
    abstract function getRootPrimaryCol();

    /**
     *
     * Does the database set the primary key value on insert, e.g. by using
     * auto-increment?
     *
     * @return bool
     *
     */
    abstract public function isRootAutoPrimary();

    /**
     * @return array
     */
    abstract public function getJoins();

    /**
     * we should look at using this to make friendly to table name conversion simplier
     *
     * @return array
     */
    public function getTableAliasMap()
    {
        $output = array();
        foreach ($this->allGateways as $friendlyName => $gateway) {
            $output[$gateway->getTable()] = $friendlyName;
        }
        return $output;
    }

    /**
     *
     * Returns the database read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection()
    {
        if (! $this->read_connection) {
            $this->read_connection = $this->connection_locator->getRead();
        }
        return $this->read_connection;
    }

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection()
    {
        if (! $this->write_connection) {
            $this->write_connection = $this->connection_locator->getWrite();
        }
        return $this->write_connection;
    }

    public function select(array $cols)
    {
        $select = $this->query_factory->newSelect($this->getReadConnection());
        $select->from($this->getRootTable());
        foreach ($this->getJoins() as $table => $join) {
            $select->join($join);
        }
        $select->cols($this->getTableCols($cols));
        return $select;
    }

    /**
     *
     * Updates a row in the table using a write connection.
     *
     * @param array $row The row array to update.
     *
     * @return bool True if the update succeeded, false if not.  (This is
     * determined by checking the number of rows affected by the query.)
     *
     */
    public function update(array $row)
    {
        $row = $this->filter->forUpdate($row);
        $parsed = $this->parseRow($row);
        foreach ($parsed as $friendlyName => $cols) {
            $result = $this->allGateways[$friendlyName]->update($cols);
            if ($result === false) {
                return false;
            }
        }
        return $row;
    }

    public function delete(array $row)
    {
        $parsed = $this->parseRow($row);
        foreach ($parsed as $friendlyName => $cols) {
            $result = $this->allGateways[$friendlyName]->delete($cols);
            if ($result === false) {
                return false;
            }
        }
        return $row;
    }

    public function insert(array $row)
    {
        $parsed = $this->parseRow($row);
        foreach ($parsed as $friendlyName => $cols) {
            $result = $this->allGateways[$friendlyName]->insert($cols);
            if ($result === false) {
                return false;
            }
            $prepend = '';
            if ($friendlyName != '__root') {
                $prepend = $friendlyName . '.';
            }
            foreach ($result as $field => $val) {
                $corrected = $prepend . $field;
                $row[$corrected] = $val;
            }
        }
        return $row;
    }

    /**
     *
     * Returns an array of fully-qualified table columns.
     *
     * @param array $cols The column names.
     *
     * @return array
     *
     */
    protected function getTableCols(array $cols = array())
    {
        $list = array();
        foreach ($cols as $col) {
            $list[] = $this->getTableCol($col);
        }
        return $list;
    }

    protected function getTableCol($col, $is_select = false)
    {
        $pieces = explode('.', $col);
        if (count($pieces) === 1) {
            $col = $pieces[0];
            $table = $this->getRootTable();
        } else {
            $friendlyName = $pieces[0];
            unset($pieces[0]);
            $col = implode('.', $pieces);
            $table = $this->leafGateways[$friendlyName]->getTable();
            if ($is_select === true) {
                $this->addAliasToCol($col, $friendlyName);
            }
        }
        return $table . '.' . $col;
    }

    protected function addAliasToCol($col, $alias)
    {
        if (! preg_match("/\sas\s/", $col)) {
            $alias_string = 'as "' . $alias . '.' . $col . '"';
            $col .= $alias_string;
        }
        return $col;
    }

    /**
     *
     * Parses a row into an multi-dimensional associative array indexed by table.
     *
     * @param array $row The row array to parse.
     *
     * @return array Parsed array.
     *
     */
    protected function parseRow(array $row)
    {
        $output = array();
        foreach ($row as $col => $val) {
            $match = explode('.', $col);
            if (count($match) === 1){
                $col = $match[0];
                $table = '__root';
            } else {
                $table = $match[0];
                $col   = $match[1];
            }
            $output[$table][$col] = $val;
        }
        return $output;
    }
}