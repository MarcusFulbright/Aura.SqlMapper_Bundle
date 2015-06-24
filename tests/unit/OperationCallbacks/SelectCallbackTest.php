<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\OperationCallbacks\SelectCallback;
use Mockery\MockInterface;

class SelectCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var MapperLocator */
    protected $locator;

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

    /** @var MockInterface */
    protected $row_mapper;

    public function setUp()
    {
        $this->locator = new MapperLocator([
            'fakeRootMapper' => function() { return $this->row_mapper;},
            'fakeBuildingMapper' => function () { return $this->row_mapper;}
        ]);
        $this->arranger = \Mockery::mock('Aura\SqlMapper_Bundle\OperationArranger');
        $this->aggregate_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
        $this->resolver = \Mockery::mock('Aura\SqlMapper_Bundle\PlaceholderResolver');
        $this->callback = new SelectCallback(
            $this->locator,
            $this->aggregate_mapper,
            $this->arranger,
            $this->resolver
        );
        $this->relation_to_mapper = [
            '__root'   => ['mapper' => 'fakeRootMapper'],
            'building' => ['mapper' => 'fakeBuildingMapper']
        ];
        $this->aggregate_mapper->shouldReceive('getRelationToMapper')->andReturn($this->relation_to_mapper);
        $this->row_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\MapperInterface');

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
            ->shouldReceive('resolve')
            ->once()
            ->with(1, [], $this->aggregate_mapper)
            ->andReturn(1);
        $this
            ->resolver
            ->shouldReceive('resolve')
            ->once()
            ->with(':__root.building', ['__root' => $root_results], $this->aggregate_mapper)
            ->andReturn(2);
        $this
            ->row_mapper
            ->shouldReceive('fetchCollectionBy')
            ->once()
            ->with('id', 1)
            ->andReturn($root_results);
        $this->row_mapper
            ->shouldReceive('fetchCollectionBy')
            ->once()
            ->with('id', 2)
            ->andReturn($building_results);

        $this->assertEquals($expected, $this->callback->__invoke($this->path_from_root));
    }
}
