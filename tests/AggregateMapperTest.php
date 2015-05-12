<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\ObjectFactory;

class AggregateMapperTest extends \PHPUnit_Framework_TestCase
{

    protected $object_factory;
    protected $mapper;

    protected function setUp()
    {
        parent::setUp();
        $this->object_factory = new ObjectFactory();
        $this->mapper = new FakeAggregateMapper($this->object_factory);
    }

    public function testNewObject()
    {
        $row = array('a' => 'a', 'b' => 'b', 'c' => 'c');
        $this->assertEquals(
            (object) $row,
            $this->mapper->newObject($row)
        );
    }

    public function testNewCollection()
    {
        $rowOne = array('a' => 'a', 'b' => 'b', 'c' => 'c');
        $rowTwo = array('a' => 'd', 'b' => 'e', 'c' => 'f');
        $rowThree = array('a' => 'g', 'b' => 'h', 'c' => 'i');
        $this->assertEquals(
            array(
                (object) $rowOne,
                (object) $rowTwo,
                (object) $rowThree
            ),
            $this->mapper->newCollection(
                array(
                    $rowOne,
                    $rowTwo,
                    $rowThree
                )
            )
    );
    }
}
