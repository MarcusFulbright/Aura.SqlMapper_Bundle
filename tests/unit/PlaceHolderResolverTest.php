<?php
namespace Aura\SqlMapper_Bundle\Tests\Unit;

class PlaceHolderResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlaceholderResolver */
    protected $resolver;

    /** @var FakeAggregateMapper */
    protected $mapper;

    public function setUp()
    {
        $this->resolver = new PlaceholderResolver();
        $this->mapper   = new FakeAggregateMapper(new ObjectFactory());
        $this->mapper->includeRelation('building', 'building.type', 'floor', 'task', 'task.type');
    }

    public function testResolveArray()
    {
        $criteria = ['code' => ':task.type.code'];
        $data = array(
            'task.type' => array(
                array(
                    'code' => 'F',
                    'id' => '3'
                )
            )
        );
        $expected = array('code' => ['F']);
        $this->assertEquals($expected, $this->resolver->resolveCriteria($criteria, $data, $this->mapper));
    }

    public function testResolveStdClass()
    {
        $criteria = ['code' => ':task.type.code'];
        $obj = new \stdClass();
        $obj->code = 'F';
        $obj->id = '3';
        $data = array(
            'task.type' => array($obj)
        );
        $expected = array('code' => ['F']);
        $this->assertEquals($expected, $this->resolver->resolveCriteria($criteria, $data, $this->mapper));
    }
}
