<?php
namespace Aura\SqlMapper_Bundle;



class RowDataExtractorUnitTest extends \PHPUnit_Framework_TestCase
{

    protected $fake_mapper;
    protected $mock_object_factory;
    protected $row_data_extractor;
    protected $aggregate_domain;
    protected $relation_map;
    protected $property_map;
    protected $expected_results;

    public function setUp()
    {
        $this->row_data_extractor = new RowDataExtractor();
        $this->mock_object_factory = \Mockery::mock('Aura\SqlMapper_Bundle\ObjectFactory');

        $this->aggregate_domain = (object) array(
            'rootid'   => 1,
            'rootname' => 'Charles',
            'branch'   => (object) array(
                'branchid'   => 2,
                'branchname' => 'Shirley',
                'leaf'       => (object) array(
                    'leafid'   => 3,
                    'leafname' => 'Erickson'
                )
            )
        );

        $this->expected_results = array(
            '__root' => (object) array(
                'mapper' => 'root',
                'rows' => array(
                    (object) array(
                        'object' => $this->aggregate_domain,
                        'data' => array(
                            'id'   => 1,
                            'name' => 'Charles'
                        )
                    )
                )
            ),
            'branch' => (object) array(
                'mapper' => 'branch',
                'rows' => array(
                    (object) array(
                        'object' => $this->aggregate_domain->branch,
                        'data' => array(
                            'id'     => 2,
                            'name'   => 'Shirley',
                            'rootid' => ':__root:root:0:id'
                        )
                    )
                )
            ),
            'branch.leaf' => (object) array(
                'mapper' => 'leaf',
                'rows' => array(
                    (object) array(
                        'object' => $this->aggregate_domain->branch->leaf,
                        'data' => array(
                            'id'       => 3,
                            'name'     => 'Erickson',
                            'branchid' => ':branch:branch:0:id'
                        )
                    )
                )
            )
        );

        $this->property_map = array(
            'rootid'               => 'root.id',
            'rootname'             => 'root.name',
            'branch.branchid'      => 'branch.id',
            'branch.branchname'    => 'branch.name',
            'branch.leaf.leafid'   => 'leaf.id',
            'branch.leaf.leafname' => 'leaf.name'
        );

        $this->relation_map = array(
            'branch' => array(
                'join_property'   => 'rootid',
                'reference_field' => 'branch.rootid',
                'owner'           => true,
                'type'            => 'hasOne'
            ),
            'branch.leaf' => array(
                'join_property'    => 'branchid',
                'reference_field'  => 'leaf.branchid',
                'owner'            => true,
                'type'             => 'hasOne'
            )
        );

        $this->fake_mapper = \Mockery::mock(
            'Aura\SqlMapper_Bundle\AbstractAggregateMapper[getRelationMap, getPropertyMap]',
            array($this->mock_object_factory)
        );

        $self = $this;
        $this->fake_mapper
            ->shouldReceive('getRelationMap')
            ->andReturnUsing(function() use($self) {
                return $self->relation_map;
            });
        $this->fake_mapper
            ->shouldReceive('getPropertyMap')
            ->andReturnUsing(function() use($self) {
                return $self->property_map;
            });
    }

    public function testGetRowData()
    {
        $this->fake_mapper->persist_order = array('__root', 'branch', 'branch.leaf');
        $results = $this->row_data_extractor->getRowData($this->aggregate_domain, $this->fake_mapper);
        $this->compareResults(
            $results,
            $this->expected_results,
            $this->fake_mapper->persist_order
        );
    }

    public function testGetRowDataShouldRespectPersistOrder()
    {
        $this->fake_mapper->persist_order = array('__root', 'branch.leaf', 'branch');
        $results = $this->row_data_extractor->getRowData($this->aggregate_domain, $this->fake_mapper);
        $this->compareResults(
            $results,
            $this->expected_results,
            $this->fake_mapper->persist_order
        );
    }

    public function testGetRowDataHasMany()
    {
        //Change the leaf relationship to has many
        $this->relation_map['branch.leaf']['type'] = 'hasMany';
        $leaf = $this->aggregate_domain->branch->leaf;
        $this->aggregate_domain->branch->leaf = array($leaf);

        //Add another leaf
        $leafTwo = clone $leaf;
        $leafTwo->leafid = 10;
        $leafTwo->leafname = "Leaf TOOO";
        $this->aggregate_domain->branch->leaf[] = $leafTwo;

        //Alter expected results with this second leaf
        $this->expected_results['branch.leaf']->rows[] = (object) array(
            'object' => $leafTwo,
            'data' => array(
                'id' => $leafTwo->leafid,
                'name' => $leafTwo->leafname,
                'branchid' => ':branch:branch:0:id'
            )
        );

        $this->fake_mapper->persist_order = array('__root', 'branch.leaf', 'branch');
        $results = $this->row_data_extractor->getRowData($this->aggregate_domain, $this->fake_mapper);

        $this->compareResults(
            $results,
            $this->expected_results,
            $this->fake_mapper->persist_order
        );
    }

    public function testGetRowDataHasManyComplex()
    {
        //Change the leaf relationship to has many
        $this->relation_map['branch.leaf']['type'] = 'hasMany';
        $leaf = $this->aggregate_domain->branch->leaf;
        $this->aggregate_domain->branch->leaf = array($leaf);

        //Add another leaf
        $leafTwo = clone $leaf;
        $leafTwo->leafid = 10;
        $leafTwo->leafname = "Leaf TOOO";
        $this->aggregate_domain->branch->leaf[] = $leafTwo;

        //Alter expected results with this second leaf
        $this->expected_results['branch.leaf']->rows[] = (object) array(
            'object' => $leafTwo,
            'data' => array(
                'id' => $leafTwo->leafid,
                'name' => $leafTwo->leafname,
                'branchid' => ':branch:branch:0:id'
            )
        );

        //Change the branch relationship to has many
        $this->relation_map['branch']['type'] = 'hasMany';
        $branch = $this->aggregate_domain->branch;
        $this->aggregate_domain->branch = array($branch);

        //Add another branch
        $branchTwo = clone $branch;
        $branchTwo->branchid = 11;
        $branchTwo->branchname = "Jarvis";
        $this->aggregate_domain->branch[] = $branchTwo;

        //Add This Branch's Leaf Array
        $leafThree = (object) array(
            'leafid' => 13,
            'leafname' => 'Richards'

        );
        $branchTwo->leaf = array($leafThree);

        //Alter expected results with this second branch
        $this->expected_results['branch']->rows[] = (object) array(
            'object' => $branchTwo,
            'data' => array(
                'id' => $branchTwo->branchid,
                'name' => $branchTwo->branchname,
                'rootid' => ':__root:root:0:id'
            )
        );

        //Alter expected results with this third leaf
        $this->expected_results['branch.leaf']->rows[] = (object) array(
            'object' => $leafThree,
            'data' => array(
                'id' => $leafThree->leafid,
                'name' => $leafThree->leafname,
                'branchid' => ':branch:branch:1:id'
            )
        );

        $this->fake_mapper->persist_order = array('__root', 'branch', 'branch.leaf',);
        $results = $this->row_data_extractor->getRowData($this->aggregate_domain, $this->fake_mapper);

        $this->compareResults(
            $results,
            $this->expected_results,
            $this->fake_mapper->persist_order
        );
    }

    public function testInverseRelationships()
    {
        //Alter the nature of the branch relationship
        $this->relation_map['branch'] = array(
            'join_property'   => 'branchid',
            'reference_field' => '__root.branchid',
            'owner'           => false,
            'type'            => 'hasOne'
        );

        //Alter expected results to include the branchid placeholder on root.
        unset($this->expected_results['branch']->rows[0]->data['rootid']);
        $this->expected_results['__root']->rows[0]->data['branchid'] = ':branch:branch:0:id';

        $results = $this->row_data_extractor->getRowData($this->aggregate_domain, $this->fake_mapper);
        $this->compareResults(
            $results,
            $this->expected_results,
            null
        );
    }

    public function testManyRoots()
    {
        //Create second root object
        $ad2 = clone $this->aggregate_domain;
        $ad2->rootid = 2;
        $ad2->rootname = 'Willem';
        $ad2->branch = (object) array(
            'branchid'   => 42,
            'branchname' => 'Chuck',
            'leaf'       => (object) array(
                'leafid'   => 15,
                'leafname' => 'Sue'
            )
        );

        //Change expected output
        $this->expected_results['__root']->rows[] = (object) array(
            'object' => $ad2,
            'data' => array(
                'name' => 'Willem',
                'id'   => 2
            )
        );
        $this->expected_results['branch']->rows[] = (object) array(
            'object' => $ad2->branch,
            'data' => array(
                'name' => 'Chuck',
                'id'   => 42,
                'rootid'     => ':__root:root:1:id'
            )
        );
        $this->expected_results['branch.leaf']->rows[] = (object) array(
            'object' => $ad2->branch->leaf,
            'data' => array(
                'name' => 'Sue',
                'id'   => 15,
                'branchid' => ':branch:branch:1:id'
            )
        );

        $results = $this->row_data_extractor->getRowData(array($this->aggregate_domain, $ad2), $this->fake_mapper);

        $this->compareResults(
            $results,
            $this->expected_results,
            null
        );
    }

    public function testManyRootsWithInverseRelationship()
    {
        //Alter the nature of the branch relationship
        $this->relation_map['branch'] = array(
            'join_property'   => 'branchid',
            'reference_field' => '__root.branchid',
            'owner'           => false,
            'type'            => 'hasOne'
        );

        //Alter expected results to include the branchid placeholder on root.
        unset($this->expected_results['branch']->rows[0]->data['rootid']);
        $this->expected_results['__root']->rows[0]->data['branchid'] = ':branch:branch:0:id';

        //Create second root object
        $ad2 = clone $this->aggregate_domain;
        $ad2->rootid = 2;
        $ad2->rootname = 'Willem';
        $ad2->branch = (object) array(
            'branchid' => 42,
            'branchname' => 'Chuck',
            'leaf' => (object) array(
                'leafid' => 15,
                'leafname' => 'Sue'
            )
        );

        //Change expected output
        $this->expected_results['__root']->rows[] = (object) array(
            'object' => $ad2,
            'data' => array(
                'name' => 'Willem',
                'id'   => 2,
                'branchid' =>':branch:branch:1:id'
            )
        );
        $this->expected_results['branch']->rows[] = (object) array(
            'object' => $ad2->branch,
            'data' => array(
                'name' => 'Chuck',
                'id'   => 42
            )
        );
        $this->expected_results['branch.leaf']->rows[] = (object) array(
            'object' => $ad2->branch->leaf,
            'data' => array(
                'name' => 'Sue',
                'id'   => 15,
                'branchid' => ':branch:branch:1:id'
            )
        );

        $results = $this->row_data_extractor->getRowData(array($this->aggregate_domain, $ad2), $this->fake_mapper);
        $this->compareResults($results, $this->expected_results);
    }

    protected function compareResults($results, $expected, $persist_order = null) {
        $i = 0;
        if ($persist_order !== null) {
            foreach ($results as $key => $mapperInfo) {
                //Ensure order is respected.
                $this->assertEquals($persist_order[$i], $key);
                $i++;
            }
        }
        //make sure values match.
        $this->assertEquals($expected, $results);
    }
}
