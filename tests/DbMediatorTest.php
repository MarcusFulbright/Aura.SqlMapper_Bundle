<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;

class DbMediatorTest extends AbstractMapperTestCase
{
    use DbResultUtil;

    /** @var DbMediator */
    protected $mediator;

    protected function setUp()
    {
        parent::setUp();
        $this->mediator = new DbMediator(
            $this->mapper_locator,
            new OperationArranger(),
            new PlaceholderResolver(),
            new RowDataExtractor(),
            new OperationCallbackFactory()
        );
    }

    protected function fetchByName($name)
    {
        return $this->mediator->select($this->aggregate_mapper, array('name' => $name));
    }

    protected function mergeWithFetched($data, $name)
    {
        $fetched = $this->fetchByName($name);
            if (! isset($data->building)) {
                $data->building = $fetched['building'][0];
                $data->building->type = $this->resolveBuildingType($data->building->type);
            }
            if (! isset($data->floor)) {
                $data->floor = $fetched['floor'][0];
            }
            if (! isset($data->task)) {
                $data->task = $fetched['task'];
                foreach ($data->task as $task) {
                    $task->type = $this->resolveTaskType($task->type);
                }
            }
        return $data;
    }

    public function testSelectWithWhereLeafOwns()
    {
        $this->assertEquals(
            $this->getHanna(),
            $this->mediator->select($this->aggregate_mapper, array('task.id' => '6'))
        );
    }

    public function testSelectWhereRootOwns()
    {
        $expected = $this->mergeDbResults(
            [
                $this->getBetty(),
                $this->getEdna(),
                $this->getHanna(),
                $this->getKara()
            ]
        );
        $this->assertEquals($expected, $this->mediator->select($this->aggregate_mapper, array('floor.id' => '2')));
    }

    public function testSelectCriteriaOnRoot()
    {
        $criteria = ['__root.name' => 'Hanna'];
        $this->assertEquals(
            $this->getHanna(),
            $this->mediator->select($this->aggregate_mapper, $criteria)
        );
    }

    public function testSelectNoCriteria()
    {
        $results = $this->mediator->select($this->aggregate_mapper);
        $this->assertArrayHasKey('__root', $results);
        $this->assertCount(12, $results['__root']);
        $this->assertArrayHasKey('building', $results);
        $this->assertCount(2, $results['building']);
        $this->assertArrayHasKey('building.type', $results);
        $this->assertCount(2, $results['building.type']);
        $this->assertArrayHasKey('floor', $results);
        $this->assertCount(3, $results['floor']);
        $this->assertArrayHasKey('task', $results);
        $this->assertCount(6, $results['task']);
        $this->assertArrayHasKey('task.type', $results);
        $this->assertCount(4, $results['task.type']);
    }

    public function testCreateOnlyRootNew()
    {
        $obj = $this->mergeWithFetched(
            (object)array(
                'id' => null,
                'name' => 'Missy'
            ),
            'Betty'
        );
        $expected = clone($obj);
        $expected->id = '13';
        $this->assertEquals($expected, $this->mediator->create($this->aggregate_mapper, $obj));
    }

    public function testCreateNewRootNewLeaf()
    {
        $obj = $this->mergeWithFetched(
            (object)array(
                'id' => null,
                'name' => 'Missy',
                'floor' => (object) array(
                    'id' => null,
                    'name' => 'Business Intelligence'
                )
            ),
            'Betty'
        );
        $expected = clone($obj);
        $expected->id = '13';
        $expected->floor->id = 4;
        $this->assertEquals($expected, $this->mediator->create($this->aggregate_mapper, $obj));
    }

    public function testCreateNewRootUpdateLeaf()
    {
        $obj = $this->mergeWithFetched(
            (object)array(
                'id' => null,
                'name' => 'Missy',
            ),
            'Betty'
        );
        $obj->building->name = 'Altered Building Name';
        $expected = clone($obj);
        $expected->id = '13';
        $this->assertEquals($expected, $this->mediator->create($this->aggregate_mapper, $obj));
    }

    public function testCreateExistingRootThrowsError()
    {
        $fetched = $this->mediator->select($this->aggregate_mapper, array('id' => 1));
        $obj = (object) $fetched['__root'][0];
        $obj->floor = (object) array(
            'id' => null,
            'name' => 'Not Going Into The DB'
        );
        $obj->building = (object)$fetched['building'][0];
        $obj->building->type = (object)$fetched['building.type'][0];
        $obj->task = [];

        $this->setExpectedException('Aura\SqlMapper_Bundle\Exception\DbOperationException');
        $this->mediator->create($this->aggregate_mapper, $obj);
    }

    public function testUpdateWithRootChangeAndNewLeaf()
    {
        $obj = $this->mergeWithFetched(
            (object) [
                'id' => '2',
                'name' => 'Altered',
                'floor' => (object)[
                    'id' => null,
                    'name' => 'Brand New Unique Floor Name'
                ]
            ],
            'Betty'
        );
        $expected = clone($obj);
        $expected->floor->id = '4';
        $this->assertEquals($expected, $this->mediator->update($this->aggregate_mapper, $obj));
    }

    public function testUpdateWithLeafChange()
    {
        $obj = $this->mergeWithFetched(
            (object) [
                'id' => '2',
                'name' => 'Betty',
            ],
            'Betty'
        );
        $obj->building->name = 'Alerted Building Name';
        $this->assertEquals($obj, $this->mediator->update($this->aggregate_mapper, $obj));
    }

    public function testUpdateWithRootChange()
    {
        $obj = $this->mergeWithFetched(
            (object) [
                'id' => '2',
                'name' => 'Altered',
            ],
            'Betty'
        );
        $this->assertEquals($obj, $this->mediator->update($this->aggregate_mapper, $obj));
    }

    public function testUpdateWithNewLeafOwner()
    {
        $obj = $this->mergeWithFetched(
            (object) [
                'id' => '2',
                'name' => 'Altered',
            ],
            'Betty'
        );
        $obj->task[] = (object)[
            'id' => null,
            'userid' => 2,
            'name' => 'newTask',
            'type' => (object) [
                'id' => '3',
                'code' => 'F',
                'decode' => 'Financials'
            ]
        ];
        $expected = clone($obj);
        $expected->task[2]->id = '7';
        $this->assertEquals($expected, $this->mediator->update($this->aggregate_mapper, $obj));
    }
}
