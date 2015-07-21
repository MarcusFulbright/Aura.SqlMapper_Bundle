<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlMapper_Bundle\Row\GatewayLocator;
use Aura\SqlQuery\QueryFactory;

class GatewayGenerator
{
    protected $user  = [
        'table' => 'aura_test_table',
        'primary' => 'id'
    ];

    protected $task = [
        'table' => 'aura_test_task',
        'primary' => 'id'
    ];

    protected $task_type = [
        'table' => 'aura_test_task_typeref',
        'primary' => 'id'
    ];

    protected $building = [
        'table' => 'aura_test_building',
        'primary' => 'id'
    ];

    protected $floor = [
        'table' => 'aura_test_floor',
        'primary' => 'id'
    ];

    protected $building_type = [
        'table' => 'aura_test_building_typeref',
        'primary' => 'id'
    ];

    /** @var ConnectionLocator */
    protected $connection_locator;

    /** @var ConnectedQueryFactory  */
    protected $query;

    /** @var Filter */
    protected $filter;

    public function __construct()
    {
        $profiler = new Profiler();
        $this->profiler = $profiler;

        $this->query = new ConnectedQueryFactory(new QueryFactory('sqlite'));
        $this->filter = new Filter();
        $this->connection_locator = new ConnectionLocator(
            function () use ($profiler) {
            $pdo = new ExtendedPdo('sqlite::memory:');
            $pdo->setProfiler($profiler);
            return $pdo;
        });
    }

    public function getProfiler()
    {
        return $this->profiler;
    }

    /** @return ConnectionLocator */
    public function getConnection()
    {
        return $this->connection_locator;
    }

    public function setUpGatewayLocator(array $entities)
    {
        $gateways = [];
        foreach ($entities as $entity) {
            $gateways[$entity.'_gateway'] = function() use ($entity) {
                return $this->getGateway($entity);
            };
        }
        return new GatewayLocator($gateways);
    }

    public function getGateway($entity)
    {
        $info = $this->$entity;
        $g = new FakeGateway($this->connection_locator, $this->query, $this->filter);
        $g->setInfo($info['table'], $info['primary']);
        return $g;
    }
}