<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\RowDataArranger;

class RowDataArrangerUnitTest extends \PHPUnit_Framework_TestCase {

    protected $results_arranger;
    protected $object_factory;
    protected $mapper;
    protected $data;
    protected $property_map = array(
        'rootid' => 'root.id',
        'rootname' => 'root.name',
        'branch.branchid' => 'branch.id',
        'branch.branchname' => 'branch.name',
        'branch.leaf.leafid' => 'leaf.id',
        'branch.leaf.leafname' => 'leaf.name'
    );
    protected $relation_map = array(
        'branch' => array(
            'join_property' => 'rootid',
            'reference_field' => 'branch.rootid',
            'owner' => true,
            'type' => 'hasOne'
        ),
        'branch.leaf' => array(
            'join_property' => 'branchid',
            'reference_field' => 'leaf.branchid',
            'owner' => true,
            'type' => 'hasOne'
        )
    );
    protected $expected = array(
        array(
            'rootid' => 1,
            'rootname' => 'Charles',
            'branch' => array(
                'branchid' => 2,
                'branchname' => 'Shirley',
                'leaf' => array(
                    'leafid' => 3,
                    'leafname' => 'Erickson'
                )
            )
        )
    );

    public function setUp()
    {
        $this->object_factory = \Mockery::mock('Aura\SqlMapper_Bundle\ObjectFactoryInterface');
        $this->results_arranger = new RowDataArranger();

        $this->data = array(
            '__root' => array(
                (object) array(
                    'id'   => 1,
                    'name' => 'Charles'
                )
            ),
            'branch' => array(
                (object) array(
                    'id'     => 2,
                    'name'   => 'Shirley',
                    'rootid' => 1
                )
            ),
            'branch.leaf' => array(
                (object) array(
                    'id'       => 3,
                    'name'     => 'Erickson',
                    'branchid' => 2
                )
            )
        );

        $this->mapper = \Mockery::mock(
            'Aura\SqlMapper_Bundle\AbstractAggregateMapper[getRelationMap, getPropertyMap]',
            array($this->object_factory)
        );

        $this->mapper->shouldReceive('getPropertyMap')->andReturnUsing(
            function () {
                return $this->property_map;
            }
        );

        $this->mapper->shouldReceive('getRelationMap')->andReturnUsing(
            function () {
                return $this->relation_map;
            }
        );
    }

    public function testArrange()
    {
        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

    public function testArrangeWithInverseRelations()
    {
        $this->relation_map['branch']['owner'] = false;
        $this->relation_map['branch']['join_property'] = 'branchid';
        $this->relation_map['branch']['reference_field'] = 'root.branchid';

        $this->data['__root'][0]->branchid = 2;
        unset($this->data['branch'][0]->rootid);

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

    public function testArrangeWithHasMany()
    {
        $this->relation_map['branch']['type'] = 'hasMany';
        $this->expected[0]['branch'] = array($this->expected[0]['branch']);

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

    public function testArrangeWithMultipleRoots()
    {
        $copy = $this->expected[0];
        $copy['rootid'] = 5;
        $copy['branch']['branchid'] = 6;
        $copy['branch']['leaf']['leafid'] = 7;
        $this->expected[] = $copy;

        $copyRoot     = clone $this->data['__root'][0];
        $copyRoot->id = 5;
        $this->data['__root'][] = $copyRoot;

        $copyBranch     = clone $this->data['branch'][0];
        $copyBranch->id = 6;
        $copyBranch->rootid = 5;
        $this->data['branch'][] = $copyBranch;

        $copyLeaf     = clone $this->data['branch.leaf'][0];
        $copyLeaf->id = 7;
        $copyLeaf->branchid = 6;
        $this->data['branch.leaf'][] = $copyLeaf;

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

    public function testArrangeWithMissingFields()
    {
        $this->expected[0]['branch']['leaf'] = null;
        unset($this->data['branch.leaf'][0]);

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );

        $this->expected[0]['branch'] = null;
        unset($this->data['branch'][0]);

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

    public function testExtraFieldsShouldBeTrimmed()
    {
        $this->expected[0]['branch']['leaf'] = null;
        $this->data['branch.leaf'][0]->branchid = 50;

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

    public function testComplexOne()
    {
        $this->relation_map['branch']['owner'] = false;
        $this->relation_map['branch']['join_property'] = 'branchid';
        $this->relation_map['branch']['reference_field'] = 'root.branchid';

        $this->relation_map['branch']['type'] = 'hasOne';
        $this->data['__root'][0]->branchid = 2;
        unset($this->data['branch'][0]->rootid);

        $this->relation_map['branch.leaf']['type'] = 'hasMany';
        $this->expected[0]['branch']['leaf'] = array($this->expected[0]['branch']['leaf']);

        //EXPECTATION
        //Second Root
        $copy = $this->expected[0];
        $copy['rootid'] = 5;
        $copy['branch']['branchid'] = 6;
        $copy['branch']['leaf'][0]['leafid'] = 7;

        //Extra leaf on Second Root
        $copy['branch']['leaf'][1] = $copy['branch']['leaf'][0];
        $copy['branch']['leaf'][1]['leafid'] = 8;

        //Add to expected.
        $this->expected[] = $copy;

        //Third root, no branch.
        $copy = $this->expected[1];
        $copy['rootid'] = 6;
        $copy['branch'] = null;
        $this->expected[] = $copy;

        //Fourth root, shared branch.
        $copy = $this->expected[0];
        $copy['rootid'] = 7;
        $this->expected[] = $copy;

        //DATA
        //Second Root
        $copyRoot     = clone $this->data['__root'][0];
        $copyRoot->id = 5;
        $copyRoot->branchid = 6;
        $this->data['__root'][] = $copyRoot;

        //Third Root
        $copyRoot     = clone $this->data['__root'][0];
        $copyRoot->id = 6;
        $copyRoot->branchid = null;
        $this->data['__root'][] = $copyRoot;

        //Fourth Root, shared branch
        $copyRoot     = clone $this->data['__root'][0];
        $copyRoot->id = 7;
        $this->data['__root'][] = $copyRoot;

        //First Branch on Root 2
        $copyBranch     = clone $this->data['branch'][0];
        $copyBranch->id = 6;
        $this->data['branch'][] = $copyBranch;

        //Second Branch on Root 2
        $copyBranch     = clone $this->data['branch'][1];
        $copyBranch->id = 7;
        $this->data['branch'][] = $copyBranch;

        //First Leaf on Root 2
        $copyLeaf     = clone $this->data['branch.leaf'][0];
        $copyLeaf->id = 7;
        $copyLeaf->branchid = 6;
        $this->data['branch.leaf'][] = $copyLeaf;

        //Second Leaf on Root 2
        $copyLeaf     = clone $this->data['branch.leaf'][1];
        $copyLeaf->id = 8;
        $this->data['branch.leaf'][] = $copyLeaf;

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

    public function testComplexTwo()
    {
        $this->relation_map['branch']['type'] = 'hasMany';
        $this->expected[0]['branch'] = array($this->expected[0]['branch']);

        $this->relation_map['branch.leaf']['type'] = 'hasMany';
        $this->expected[0]['branch'][0]['leaf'] = array($this->expected[0]['branch'][0]['leaf']);

        //Second Root
        $copy = $this->expected[0];
        $copy['rootid'] = 5;
        $copy['branch'][0]['branchid'] = 6;
        $copy['branch'][0]['leaf'][0]['leafid'] = 7;

        //Extra leaf on Second Root
        $copy['branch'][0]['leaf'][1] = $copy['branch'][0]['leaf'][0];
        $copy['branch'][0]['leaf'][1]['leafid'] = 8;

        //Second branch on Root 2
        $copy['branch'][1] = $copy['branch'][0];
        $copy['branch'][1]['branchid'] = 7;
        $copy['branch'][1]['leaf'] = array();

        //Add to expected.
        $this->expected[] = $copy;

        //Third root, no branch.
        $copy = $this->expected[1];
        $copy['rootid'] = 6;
        $copy['branch'] = array();
        $this->expected[] = $copy;

        //Second Root
        $copyRoot     = clone $this->data['__root'][0];
        $copyRoot->id = 5;
        $this->data['__root'][] = $copyRoot;

        //Third Root
        $copyRoot     = clone $this->data['__root'][0];
        $copyRoot->id = 6;
        $this->data['__root'][] = $copyRoot;

        //First Branch on Root 2
        $copyBranch     = clone $this->data['branch'][0];
        $copyBranch->id = 6;
        $copyBranch->rootid = 5;
        $this->data['branch'][] = $copyBranch;

        //Second Branch on Root 2
        $copyBranch     = clone $this->data['branch'][1];
        $copyBranch->id = 7;
        $this->data['branch'][] = $copyBranch;

        //First Leaf on Root 2
        $copyLeaf     = clone $this->data['branch.leaf'][0];
        $copyLeaf->id = 7;
        $copyLeaf->branchid = 6;
        $this->data['branch.leaf'][] = $copyLeaf;

        //Second Leaf on Root 2
        $copyLeaf     = clone $this->data['branch.leaf'][1];
        $copyLeaf->id = 8;
        $this->data['branch.leaf'][] = $copyLeaf;

        $results = $this->results_arranger->arrangeRowData($this->data, $this->mapper);
        $this->assertEquals(
            $this->expected,
            $results
        );
    }

}