<?php
namespace Aura\SqlMapper_Bundle\Tests\Unit;

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

    protected function createStdClass(array $props)
    {
        $obj = new \stdClass();
        foreach ($props as $prop => $value) {
            $obj->$prop = $value;
        }
        return $obj;
    }

    public function testGetPathToRoot()
    {
        $criteria = array('building.type.code' => 'DE');
        $expected = array(
            $this->createStdClass(array(
                'criteria' => array('code' => 'DE'),
                'relation_name' => 'building.type',
                'fields' => array('code')
            )),
            $this->createStdClass(array(
                'criteria' => array('type' => ':building.type.code'),
                'relation_name' => 'building',
                'fields' => array('type')
            )),
            $this->createStdClass(array(
                'criteria' => array('building' => ':building.id'),
                'relation_name' => '__root',
                'fields' => array('building')
            ))
        );
        $this->assertEquals($expected, $this->resolver->getPathToRoot($this->mapper, $criteria));
    }

    public function testGetPathToRootNotOwning()
    {
        $criteria = array('task.type.code' => 'F');
        $expected = array(
            $this->createStdClass(array(
                'criteria' => array('code' => 'F'),
                'relation_name' => 'task.type',
                'fields' => array('code')
            )),
            $this->createStdClass(array(
                'criteria' => array('type' => ':task.type.code'),
                'relation_name' => 'task',
                'fields' => array('type', 'userid')
            )),
            $this->createStdClass(array(
                'criteria' => array('id' => ':task.userid'),
                'relation_name' => '__root',
                'fields' => array('id')
            ))
        );
        $this->assertEquals($expected, $this->resolver->getPathToRoot($this->mapper, $criteria));
    }

    public function testGetPathFromRoot()
    {
        $criteria = array('__root.id' => 1);
        $expected = array(
            $this->createStdClass(array(
                'relation_name' => '__root',
                'criteria' => array('id' => 1),
                'fields' => array('id')
            )),
            $this->createStdClass(array(
                'relation_name' => 'building',
                'criteria' => array('id' => ':__root.building'),
                'fields' => array('id')
            )),
            $this->createStdClass(array(
                'relation_name' => 'building.type',
                'criteria' => array('code' => ':building.type'),
                'fields' => array('code')
            )),
            $this->createStdClass(array(
                'relation_name' => 'floor',
                'criteria' => array('id' => ':__root.floor'),
                'fields' => array('id')
            )),
            $this->createStdClass(array(
                'relation_name' => 'task',
                'criteria' => array('userid' => ':__root.id'),
                'fields' => array('userid')
            )),
            $this->createStdClass(array(
                'relation_name' => 'task.type',
                'criteria' => array('code' => ':task.type'),
                'fields' => array('code')
            ))
        );
        $this->assertEquals($expected, $this->resolver->getPathFromRoot($this->mapper, $criteria));
    }
}
