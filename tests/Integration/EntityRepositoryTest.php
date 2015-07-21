<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\Tests\Fixtures\EntityMapperGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\GatewayGenerator;
use Aura\SqlMapper_Bundle\Tests\Fixtures\SqliteFixture;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Utils\UserEntityUtil;

class EntityRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use UserEntityUtil;

    /** @var  EntityRepository */
    protected $repository;

    public function setUp()
    {
        $gateway_gen = new GatewayGenerator();
        $gateway_locator = $gateway_gen->setUpGatewayLocator(['user']);
        $mapper_gen = new EntityMapperGenerator();
        $mapper_locator = $mapper_gen->getMapperLocator(['user' => null], $gateway_locator);
        $fixtures = new SqliteFixture($gateway_gen->getConnection()->getWrite());
        $fixtures->exec();
                $this->repository = new EntityRepository($mapper_locator);
    }

    public function testFetchCollection()
    {
        $this->assertEquals(
            [$this->getBetty()],
            $this->repository->fetchCollection('user_mapper', ['id' => '2'])
        );
    }

    public function testFetchObject()
    {
        $this->assertEquals(
            $this->getBetty(),
            $this->repository->fetchObject('user_mapper', ['id' => '2'])
        );
    }

    public function testSelect()
    {
        $this->assertEquals(
            [$this->getBetty()->toDbData()],
            $this->repository->select('user_mapper', ['id' => '2'])
        );
    }

    public function testUpdate()
    {
        $betty = $this->getBetty();
        $betty->setName('new name');
        $this->assertTrue($this->repository->update('user_mapper', $betty));
    }

    public function testCreate()
    {
        $new_entry = $this->newUser(null, 'new_entry', '1', '2');
        $this->assertTrue($this->repository->create('user_mapper', $new_entry));
    }

    public function testDelete()
    {
        $this->assertTrue(
            $this->repository->delete('user_mapper',
                $this->getBetty()
            )
        );
    }
}