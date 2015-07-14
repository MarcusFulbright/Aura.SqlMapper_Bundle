<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Tests\Fixtures\AbstractIntegrationTestCase;

class EntityRepositoryTest extends AbstractIntegrationTestCase
{
    public function setUp()
    {
        $this->setUpEntities();
        $this->loadFixtures();
    }

    public function testFetchCollection()
    {
        $this->assertEquals(
            [$this->formatRecordToobject($this->getBetty(), true)],
            $this->entity_repository->fetchCollection('aura_test_table', ['id' => '2'])
        );
    }

    public function testFetchObject()
    {
        $this->assertEquals(
            $this->formatRecordToobject($this->getBetty(), true),
            $this->entity_repository->fetchObject('aura_test_table', ['id' => '2'])
        );
    }

    public function testSelect()
    {
        $this->assertEquals(
            [(array)$this->formatRecordToobject($this->getBetty(), true)],
            $this->entity_repository->select('aura_test_table', ['id' => '2'])
        );
    }

    public function testUpdate()
    {
        $betty = $this->formatRecordToobject($this->getBetty());
        $betty->building = $betty->building->id;
        $betty->floor = $betty->floor->id;
        unset($betty->tasks);
        $betty->name = 'new name';
        $this->assertTrue($this->entity_repository->update('aura_test_table', $betty));
    }

    public function testCreate()
    {
        $new_entry = (object) [
            'id' => null,
            'name' => 'new_entry',
            'building' => '1',
            'floor' => '2'
        ];
        $this->assertTrue($this->entity_repository->create('aura_test_table', $new_entry));
    }

    public function testDelete()
    {
        $this->assertTrue(
            $this->entity_repository->delete('aura_test_table',
                $this->formatRecordToobject($this->getBetty())
            )
        );
    }
}