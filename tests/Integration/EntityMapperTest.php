<?php
namespace Aura\SqlMapper_Bundle\Test\Integration;

use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\Row\GatewayLocator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Utils\Assertions;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\User;
use Aura\SqlMapper_Bundle\Tests\Fixtures\EntityMapperGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\FakeEntityMapper;
use Aura\SqlMapper_Bundle\Tests\Fixtures\GatewayGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\SqliteFixture;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Utils\UserEntityUtil;

class EntityMapperTest extends \PHPUnit_Framework_TestCase
{
    use Assertions;
    use UserEntityUtil;

    /** @var FakeEntityMapper */
    protected $mapper;

    /** @var GatewayLocator */
    protected $locator;

    /** @var Profiler */
    protected $profiler;

    protected function setUp()
    {
        $gateway_gen = new GatewayGenerator();
        $mapper_gen = new EntityMapperGenerator();
        $fixtures = new SqliteFixture($gateway_gen->getConnection()->getDefault());
        $fixtures->exec();
        $this->profiler = $gateway_gen->getProfiler();

        $this->locator = $gateway_gen->setUpGatewayLocator(['user']);
        $this->mapper = $mapper_gen->getUser($this->locator);
    }

    public function testGetIdentityValue()
    {
        $obj = new User();
        $obj->setId(88);

        $expect = 88;
        $actual = $this->mapper->getIdentityValue($obj);
        $this->assertEquals($expect, $actual);
    }

    public function testFetchObject()
    {
        $actual = $this->mapper->fetchObjectBy(['id' => 1]);
        $expect = $this->getAnna();

        $this->assertEquals($expect, $actual);

        $actual = $this->mapper->fetchObjectBy(['id' => 0]);
        $this->assertFalse($actual);
    }

    public function testFetchObjects()
    {
        $actual = $this->mapper->fetchObjectsBy(['id' => [1, 2, 3]], 'id');
        $expect = [
            '1' => $this->getAnna(),
            '2' => $this->getBetty(),
            '3' => $this->getClara()
        ];
        $this->assertEquals($expect, $actual);

        $actual = $this->mapper->fetchObjectsBy(['id' => 0], 'id');
        $this->assertSame(array(), $actual);
    }

    public function testFetchCollection()
    {
        $actual = $this->mapper->fetchCollectionBy(['id' => [1, 2, 3]]);
        $expect = [
            $this->getAnna(),
            $this->getBetty(),
            $this->getClara()
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
                $this->getAnna(),
                $this->getDonna()
            ],
            '2' => [
                $this->getBetty(),
                $this->getEdna()
            ],
            '3' => [
                $this->getClara(),
                $this->getFiona()
            ],
        ];

        $this->assertEquals($expect, $actual);
    }

    public function testInsert()
    {
        $object = new User();
        $object->setId(null);
        $object->setName('Mona');
        $object->setBuilding('10');
        $object->setFloor('99');

        $affected = $this->mapper->insert($object);
        $this->assertTrue($affected == 1);
        $this->assertEquals(13, $object->getId());

        // did it insert?
        $actual = $this->mapper->fetchObjectBy(['id' => 13]);
        $this->assertEquals('13', $actual->getId());
        $this->assertEquals('Mona', $actual->getName());

        // try to insert again, should fail
        $this->silenceErrors();
        $this->assertFalse($this->mapper->insert($object));
    }

    public function testUpdate()
    {
        // fetch an object, then modify and update it
        $object = $this->mapper->fetchObjectBy(['name' => 'Anna']);
        $object->setName('Annabelle');
        $affected = $this->mapper->update($object);

        // did it update?
        $this->assertTrue($affected == 1);
        $actual = $this->mapper->fetchObjectBy(['name' => 'Annabelle']);
        $this->assertEquals($actual, $object);

        // did anything else update?
        $actual = $this->mapper->fetchObjectBy(['id' => 2]);
        $this->assertEquals('2', $actual->getId());
        $this->assertEquals('Betty', $actual->getName());
    }

    public function testUpdateOnlyChanges()
    {
        // fetch an object, retain its original data, then change it
        $object = $this->mapper->fetchObjectBy(['name' => 'Anna']);
        $initial_data = $object->toDbData();
        $object->setName('Annabelle');

        // update with profiling turned on
        $this->profiler->setActive(true);
        $affected = $this->mapper->update($object, $initial_data);
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
        $actual = $this->locator->__get('user_gateway')->select()->fetchAll();
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

    protected function silenceErrors()
    {
        $conn = $this->locator->__get('user_gateway')->getWriteConnection();
        $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_SILENT);
    }
}
