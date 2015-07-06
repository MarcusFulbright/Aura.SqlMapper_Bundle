<?php
namespace Aura\SqlMapper_Bundle;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlQuery\QueryFactory;

class RowObjectBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var RowObjectBuilder */
    protected $builder;

    protected $betty = [
        'id' => '2',
        'firstName' => 'Betty',
        'buildingNumber' => '1',
        'floor' => '2'
    ];

    public function setUp()
    {
        $connection_locator = new ConnectionLocator(function() {
            return new ExtendedPdo('sqlite::memory:');
        });

        $fixture = new SqliteFixture(
            $connection_locator->getWrite(),
            'aura_test_table'
        );
        $fixture->exec();
        $gateway = new FakeGateway(
            $connection_locator,
            new ConnectedQueryFactory(new QueryFactory('sqlite')),
            new Filter()
        );
        $mapper = new FakeMapper(
            $gateway,
            new ObjectFactory(),
            new Filter()
        );
        $factories = ['__root' => function() use ($mapper){return $mapper;}];
        $locator = new RowMapperLocator($factories);
        $this->builder = new RowObjectBuilder($locator);
    }

    public function testFetchCollection()
    {
        $this->assertEquals(
            [(object)$this->betty],
            $this->builder->fetchCollection('__root', ['id' => '2'])
        );
    }

    public function testFetchObject()
    {
        $this->assertEquals(
            (object)$this->betty,
            $this->builder->fetchObject('__root', ['id' => '2'])
        );
    }

    public function testSelect()
    {
        $this->assertEquals(
            [$this->betty],
            $this->builder->select('__root', ['id' => '2'])
        );
    }

    public function testUpdate()
    {
        $betty = (object)$this->betty;
        $betty->firstName = 'new firstName';
        $this->assertTrue($this->builder->update('__root', $betty));
    }

    public function testCreate()
    {
        $new_entry = (object) [
            'id' => null,
            'firstName' => 'new_entry',
            'buildingNumber' => '1',
            'floor' => '2'
        ];
        $this->assertTrue($this->builder->create('__root', $new_entry));
    }

    public function testDelete()
    {
        $this->assertTrue($this->builder->delete('__root', (object)$this->betty));
    }
}