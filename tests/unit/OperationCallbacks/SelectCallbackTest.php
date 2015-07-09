<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\OperationCallbacks\SelectCallback;
use Mockery\MockInterface;

class SelectCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var MockInterface */
    protected $row_builder;

    /** @var MockInterface */
    protected $arranger;

    /** @var MockInterface */
    protected $aggregate_mapper;

    /** @var MockInterface */
    protected $resolver;

    /** @var SelectCallback */
    protected $callback;

    /** @var array */
    protected $path_from_root;

    /** @var array */
    protected $relation_to_mapper;

    public function setUp()
    {
        $this->row_builder = \Mockery::mock('Aura\SqlMapper_Bundle\RowObjectBuilder');
        $this->arranger = \Mockery::mock('Aura\SqlMapper_Bundle\OperationArranger');
        $this->aggregate_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
        $this->resolver = \Mockery::mock('Aura\SqlMapper_Bundle\PlaceholderResolver');
        $this->callback = new SelectCallback(
            $this->aggregate_mapper,
            $this->row_builder,
            $this->arranger,
            $this->resolver
        );
        $this->relation_to_mapper = [
            '__root'   => ['mapper' => 'fakeRootMapper'],
            'building' => ['mapper' => 'fakeBuildingMapper']
        ];
        $this->aggregate_mapper->shouldReceive('getRelationToMapper')->andReturn($this->relation_to_mapper);

        $this->path_from_root = [
            (object)[
                'relation_name' => '__root',
                'criteria' => ['id' => 1],
                'fields' => ['id']
            ],
            (object)[
                'relation_name' => 'building',
                'criteria' => ['id' => ':__root.building'],
                'fields' => ['id']
            ]
        ];
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testInvoke()
    {
        $root_results = [
            (object)[
                'id' => 1,
                'building' => 2
            ]
        ];
        $building_results = [
            (object)[
                'id' => 2,
                'name' => 'Building'
            ]
        ];
        $expected = ['__root' => $root_results, 'building' => $building_results];

        $this
            ->resolver
            ->shouldReceive('resolveCriteria')
            ->once()
            ->with(['id' => 1], [], $this->aggregate_mapper)
            ->andReturn(['id' => 1]);
        $this
            ->resolver
            ->shouldReceive('resolveCriteria')
            ->once()
            ->with(['id' => ':__root.building'], ['__root' => $root_results], $this->aggregate_mapper)
            ->andReturn(['building' => 2]);
        $this
            ->row_builder
            ->shouldReceive('fetchCollection')
            ->once()
            ->with('fakeRootMapper',['id' =>  1])
            ->andReturn($root_results);
        $this->row_builder
            ->shouldReceive('fetchCollection')
            ->once()
            ->with('fakeBuildingMapper',['building' => 2])
            ->andReturn($building_results);

        $this->assertEquals($expected, $this->callback->__invoke($this->path_from_root));
    }
}
