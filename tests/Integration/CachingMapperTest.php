<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Tests\Fixtures\AbstractIntegrationTestCase;
use Aura\SqlMapper_Bundle\Tests\Fixtures\FakeEntityMapper;
use Aura\SqlMapper_Bundle\Tests\Fixtures\FakeGateway;

class CachingMapperTest extends AbstractIntegrationTestCase
{
    /** @var  FakeEntityMapper */
    protected $mapper;

    /** @var \ReflectionClass */
    protected $reflection;

    /** @var FakeGateway */
    protected $gateway;

    protected function setUp()
    {
        $this->setUpEntities();
        $this->mapper = $this->factories['aura_test_table']();
        $this->reflection = new \ReflectionClass($this->mapper);
        $this->gateway = $this->gateways['aura_test_table'];
        $this->loadFixtures();
    }

    /**
     *
     * Returns an accessible ReflectionMethod by name.
     *
     * @param string $name The name of the method
     *
     * @return \ReflectionMethod
     *
     */
    protected function getProtectedMethod($name)
    {
        $method = $this->reflection->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testGetIdentityValue()
    {
        $object = (object) [
            'id' => 88
        ];

        $expect = 88;
        $actual = $this->mapper->getIdentityValue($object);
        $this->assertSame($expect, $actual);

    }

    public function testFetchObject()
    {
        $actual = $this->mapper->fetchObjectBy(['id' => 1]);
        $expect = (object) [
            'id' => '1',
            'name' => 'Anna',
            'building' => '1',
            'floor' => '1',
        ];
        $this->assertEquals($expect, $actual);

        $actual = $this->mapper->fetchObjectBy(['id' => 0]);
        $this->assertFalse($actual);
    }

    public function testFetchObjects()
    {
        $actual = $this->mapper->fetchObjectsBy(['id' => [1, 2, 3]], 'id');
        $expect = [
            '1' => (object) [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            '2' => (object) [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            '3' => (object) [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];
        $this->assertEquals($expect, $actual);

        $actual = $this->mapper->fetchObjectsBy(['id' => 0], 'id');
        $this->assertSame(array(), $actual);
    }

    public function testFetchCollection()
    {
        $actual = $this->mapper->fetchCollectionBy(['id' => [1, 2, 3]]);
        $expect = [
            (object) [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            (object) [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            (object) [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];
        $this->assertEquals($expect, $actual);

        $actual = $this->mapper->fetchCollectionBy(['id' => [0]]);
        $this->assertSame(array(), $actual);
    }

    public function testFetchCollections()
    {
        $actual = $this->mapper->fetchCollectionsBy(['building' => 1], 'floor');
        $expect = [
            '1' => [
                (object) [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                (object) [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            '2' => [
                (object) [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                (object) [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            '3' => [
                (object) [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                (object) [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $this->assertEquals($expect, $actual);
    }

    public function testInsert()
    {
        $object = (object) [
            'id' => null,
            'name' => 'Mona',
            'building' => '10',
            'floor' => '99',
        ];

        $affected = $this->mapper->insert($object);
        $this->assertTrue($affected == 1);
        $this->assertEquals(13, $object->id);

        // did it insert?
        $actual = $this->mapper->fetchObjectBy(['id' => 13]);
        $this->assertEquals('13', $actual->id);
        $this->assertEquals('Mona', $actual->name);

        // try to insert again, should fail
        $this->silenceErrors();
        $this->assertFalse($this->mapper->insert($object));
    }

    public function testUpdate()
    {
        // fetch an object, then modify and update it
        $object = $this->mapper->fetchObjectBy(['name' => 'Anna']);
        $object->name = 'Annabelle';
        $affected = $this->mapper->update($object);

        // did it update?
        $this->assertTrue($affected == 1);
        $actual = $this->mapper->fetchObjectBy(['name' => 'Annabelle']);
        $this->assertEquals($actual, $object);

        // did anything else update?
        $actual = $this->mapper->fetchObjectBy(['id' => 2]);
        $this->assertEquals('2', $actual->id);
        $this->assertEquals('Betty', $actual->name);
    }

    public function testUpdateOnlyChanges()
    {
        // fetch an object, retain its original data, then change it
        $object = $this->mapper->fetchObjectBy(['name' => 'Anna']);
        $object->name = 'Annabelle';

        // update with profiling turned on
        $this->profiler->setActive(true);
        $affected = $this->mapper->update($object);
        $this->profiler->setActive(false);

        // check the profile
        $profiles = $this->profiler->getProfiles();
        $expect = '
            UPDATE "aura_test_table"
            SET
                "name" = :name
            WHERE
                id = :id
        ';
        $this->assertSameSql($expect, $profiles[0]['statement']);
    }

    public function testDelete()
    {
        // fetch an object, then delete it
        $object = $this->mapper->fetchObjectBy(['name' => 'Anna']);
        $this->mapper->delete($object);

        // did it delete?
        $actual = $this->mapper->fetchObjectBy(['name' => 'Anna']);
        $this->assertFalse($actual);

        // do we still have everything else?
        $actual = $this->gateway->select()->fetchAll();
        $expect = 11;
        $this->assertEquals($expect, count($actual));
    }

    public function testSelect()
    {
        $select = $this->mapper->select();
        $expect = '
            SELECT
                "aura_test_table"."id" AS "id",
                "aura_test_table"."name" AS "name",
                "aura_test_table"."building" AS "building",
                "aura_test_table"."floor" AS "floor"
            FROM
                "aura_test_table"
        ';
        $actual = (string) $select;
        $this->assertSameSql($expect, $actual);
    }

    public function testExcludeSingleIdFromSelect()
    {
        $method = $this->getProtectedMethod('excludeIdsFromSelect');
        $select = $this->mapper->select();
        $method->invoke($this->mapper, $select, 13);
        $expect = '
            SELECT
                "aura_test_table"."id" AS "id",
                "aura_test_table"."name" AS "name",
                "aura_test_table"."building" AS "building",
                "aura_test_table"."floor" AS "floor"
            FROM
                "aura_test_table"
            WHERE
                "aura_test_table"."id" != :id
        ';
        $actual = (string) $select;
        $this->assertSameSql($expect, $actual);
    }

    public function testExcludeMultipleIdsFromSelect()
    {
        $method = $this->getProtectedMethod('excludeIdsFromSelect');
        $select = $this->mapper->select();
        $method->invoke($this->mapper, $select, array(1, 2, 3));
        $expect = '
            SELECT
                "aura_test_table"."id" AS "id",
                "aura_test_table"."name" AS "name",
                "aura_test_table"."building" AS "building",
                "aura_test_table"."floor" AS "floor"
            FROM
                "aura_test_table"
            WHERE
                "aura_test_table"."id" NOT IN (:id)
        ';
        $actual = (string) $select;
        $this->assertSameSql($expect, $actual);
    }

    public function testVarDumpingQueryResults() {
        $this->mapper->fetchObjectsBy(['floor' => 1], 'id');
        $this->mapper->fetchObjectsBy(['building' => 1], 'id');
    }

    protected function silenceErrors()
    {
        $conn = $this->gateway->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }
}
