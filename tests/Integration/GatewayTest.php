<?php
namespace Aura\SqlMapper_Bundle\Tests\Integration;

use Aura\SqlMapper_Bundle\Tests\Fixtures\AbstractIntegrationTestCase;
use Aura\SqlMapper_Bundle\Tests\Fixtures\FakeGateway;

class GatewayTest extends AbstractIntegrationTestCase
{
    /** @var FakeGateway */
    protected $gateway;

    protected function setUp()
    {
        $this->setUpEntities();
        $this->loadFixtures();
        $this->gateway = $this->gateways['aura_test_table'];

    }

    public function testGetPrimaryCol()
    {
        $expect = 'id';
        $actual = $this->gateway->getPrimaryCol();
        $this->assertSame($expect, $actual);
    }

    public function testGetTable()
    {
        $expect = 'aura_test_table';
        $actual = $this->gateway->getTable();
        $this->assertSame($expect, $actual);
    }

    public function testSelect()
    {
        $select = $this->gateway->select([
            'id',
            'name',
            'building',
        ]);

        $expect = '
            SELECT
                "aura_test_table"."id",
                "aura_test_table"."name",
                "aura_test_table"."building"
            FROM
                "aura_test_table"
        ';
        $actual = (string) $select;
        $this->assertSameSql($expect, $actual);
    }

    public function testInsert()
    {
        $row = [
            'id' => null,
            'name' => 'Mona',
            'building' => 10,
        ];

        $row = $this->gateway->insert($row);
        $this->assertTrue(is_array($row));
        $this->assertEquals(13, $row['id']);

        // did it insert?
        $actual = $this->gateway->select(['id', 'name', 'building'])
            ->where('id = ?', 13)
            ->fetchOne();

        $expect = [
            'id' => '13',
            'name' => 'Mona',
            'building' => '10'
        ];

        $this->assertEquals($actual, $expect);

        // silence errors and try to insert again on a unique col ("name")
        $this->silenceErrors();
        $this->assertFalse($this->gateway->insert($row));
    }

    public function testUpdate()
    {
        // fetch an object, then modify and update it
        $row = $this->gateway->fetchRowBy(['name' => 'Anna']);
        $row['name'] = 'Annabelle';
        $row = $this->gateway->update($row);

        // did it update?
        $this->assertTrue(is_array($row));
        $actual = $this->gateway->fetchRowBy(['name' => 'Annabelle']);
        $this->assertEquals($actual, $row);

        // did anything else update?
        $actual = $this->gateway->fetchRowBy(['id' => 2], ['id', 'name']);
        $expect = ['id' => '2', 'name' => 'Betty'];
        $this->assertEquals($actual, $expect);

        // silence errors and try to update a unique col (name)
        $this->silenceErrors();
        $row['name'] = 'Betty';
        $this->assertFalse($this->gateway->update($row));
    }

    public function testDelete()
    {
        // fetch an object, then delete it
        $row = $this->gateway->fetchRowBy(['name' => 'Anna']);
        $this->gateway->delete($row);

        // did it delete?
        $actual = $this->gateway->fetchRowsBy(['name' => 'Anna']);
        $this->assertSame(array(), $actual);

        // do we still have everything else?
        $actual = $this->gateway->select()->fetchAll();
        $expect = 11;
        $this->assertEquals($expect, count($actual));
    }

    protected function silenceErrors()
    {
        $conn = $this->gateway->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }
}
