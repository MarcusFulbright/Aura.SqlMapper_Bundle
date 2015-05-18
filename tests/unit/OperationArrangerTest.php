<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Test QueryResolverTest
 * @package Aura\SqlMapper_Bundle
 */
class OperationArrangerTest extends \PHPUnit_Framework_TestCase
{
    /** @var OperationArranger */
    protected $resolver;

    /** @var FakeAggregateMapper */
    protected $mapper;

    public function setUp()
    {
        $this->resolver = new OperationArranger();
        $this->mapper   = new FakeAggregateMapper(new ObjectFactory());
        $this->mapper->includeRelation('building', 'building.type', 'floor', 'task', 'task.type');
    }

    public function testArrangeForSelectBuildingType()
    {
        $this->assertEquals(
            array_merge(
                $this->getBuildingTypeResult(array('code' => 'DE')),
                $this->getBuildingResult(array('type' => ':building.type.code')),
                $this->getRootResult(array('building' => ':building.id')),
                $this->getFloorResult(array('id' => ':__root.floorID')),
                $this->getTaskResult(array('userid' => ':__root.id')),
                $this->getTaskTypeResult(array('code' => ':task.typeCode'))
            ),
            $this->resolver->arrangeForSelect($this->mapper, array('building.type.code' => 'DE'))
        );
    }

    public function testArrangeForSelectBuilding()
    {
        $this->assertEquals(
            array_merge(
                $this->getBuildingResult(array('name' => 'batman')),
                $this->getBuildingTypeResult(array('code' => ':building.typeCode')),
                $this->getRootResult(array('building' => ':building.id')),
                $this->getFloorResult(array('id' => ':__root.floorID')),
                $this->getTaskResult(array('userid' => ':__root.id')),
                $this->getTaskTypeResult(array('code' => ':task.typeCode'))
            ),
            $this->resolver->arrangeForSelect($this->mapper, array('building.name' => 'batman'))
        );
    }

    public function testArrangeForSelectFloor()
    {
        $this->assertEquals(
            array_merge(
                $this->getFloorResult(array('id' => 2)),
                $this->getRootResult(array('floor' => ':floor.id')),
                $this->getBuildingResult(array('id' => ':__root.buildingID')),
                $this->getBuildingTypeResult(array('code' => ':building.typeCode')),
                $this->getTaskResult(array('userid' => ':__root.id')),
                $this->getTaskTypeResult(array('code' => ':task.typeCode'))
            ),
            $this->resolver->arrangeForSelect($this->mapper, array('floor.id' => 2))
        );
    }

    public function testArrangeForSelectNoCriteria()
    {
        $this->assertEquals(
            array_merge(
                $this->getRootResult(),
                $this->getBuildingResult(array('id' => ':__root.buildingID')),
                $this->getBuildingTypeResult(array('code' => ':building.typeCode')),
                $this->getTaskResult(array('userid' => ':__root.id')),
                $this->getTaskTypeResult(array('code' => ':task.typeCode')),
                $this->getFloorResult(array('id' => ':__root.floorID'))
            ),
            $this->resolver->arrangeForSelect($this->mapper)
        );
    }

    protected function getBuildingTypeResult($criteria)
    {
        return array(
            'building.type' => array(
                'mapper' => 'aura_test_building_typeref',
                'fields' => array(
                    0 => 'id',
                    1 => 'code',
                    2 => 'decode'
                ),
                'relations' => array(
                    0 => array(
                        'relation_name' => 'building.type',
                        'other_side' => 'building'
                    )
                ),
                'criteria' => $criteria
            )
        );
    }

    protected function getBuildingResult($criteria)
    {
        return array(
            'building' => array(
                'mapper' => 'aura_test_building',
                'fields' => array(
                    0 => 'id',
                    1 => 'name',
                    2 => 'type'
                ),
                'relations' => array(
                    0 => array(
                        'relation_name' => 'building',
                        'other_side' => '__root'
                    ),
                    1 => array(
                        'relation_name' => 'building.type',
                        'other_side' => 'building.type'
                    )
                ),
                'criteria' => $criteria
            )
        );
    }

    protected function getRootResult($criteria = null)
    {
        $result = array(
            '__root' => array(
                'mapper' => 'aura_test_table',
                'fields' => array(
                    0 => 'id',
                    1 => 'name',
                    2 => 'building',
                    3 => 'floor'
                ),
                'relations' => array(
                    0 => array(
                        'relation_name' => 'building',
                        'other_side' => 'building'
                    ),
                    1 => array(
                        'relation_name' => 'floor',
                        'other_side' => 'floor'
                    ),
                    2 => array(
                        'relation_name' => 'task',
                        'other_side' => 'task'
                    )
                ),
            )
        );
        if (isset($criteria)) {
            $result['__root']['criteria'] = $criteria;
        }
        return $result;
    }

    protected function getFloorResult($criteria)
    {
        return array(
            'floor' => array(
                'mapper' => 'aura_test_floor',
                'fields' => array(
                    0 => 'id',
                    1 => 'name'
                ),
                'relations' => array(
                    0 => array(
                        'relation_name' => 'floor',
                        'other_side' => '__root'
                    )
                ),
                'criteria' => $criteria
            ),
        );
    }

    protected function getTaskResult($criteria)
    {
        return array(
            'task' => array(
                'mapper' => 'aura_test_task',
                'fields' => array(
                    0 => 'id',
                    1 => 'userid',
                    2 => 'name',
                    3 => 'type'
                ),
                'relations' => array(
                    0 => array(
                        'relation_name' => 'task',
                        'other_side' => '__root'
                    ),
                    1 => array(
                        'relation_name' => 'task.type',
                        'other_side' => 'task.type'
                    )
                ),
                'criteria' => $criteria
            )
        );
    }

    protected function getTaskTypeResult($criteria)
    {
        return array(
            'task.type' => array(
                'mapper' => 'aura_test_task_typeref',
                'fields' => array(
                    0 => 'code',
                    1 => 'decode'
                ),
                'relations' => array(
                    0 => array(
                        'relation_name' => 'task.type',
                        'other_side' => 'task'
                    )
                ),
                'criteria' => $criteria
            )
        );
    }
}
