<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\RowMapperLocator;
use Aura\SqlMapper_Bundle\OperationCallbacks\SelectIdentifierCallback;
use Mockery\MockInterface;

class SelectIdentifierCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var RowMapperLocator */
    protected $locator;

    /** @var MockInterface */
    protected $arranger;

    /** @var MockInterface */
    protected $aggregate_mapper;

    /** @var MockInterface */
    protected $resolver;

    /** @var SelectIdentifierCallback */
    protected $callback;

    /** @var array */
    protected $path_to_root;

    /** @var array */
    protected $relation_to_mapper;

    /** @var MockInterface */
    protected $row_mapper;

    /** @var MockInterface */
    protected $query;

    public function setUp()
    {
        $this->locator = new RowMapperLocator([
            'fakeRootMapper' => function() { return $this->row_mapper;},
            'fakeBuildingMapper' => function () { return $this->row_mapper;}
        ]);
        $this->arranger = \Mockery::mock('Aura\SqlMapper_Bundle\OperationArranger');
        $this->aggregate_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
        $this->resolver = \Mockery::mock('Aura\SqlMapper_Bundle\PlaceholderResolver');
        $this->callback = new SelectIdentifierCallback(
            $this->aggregate_mapper,
            $this->locator,
            $this->arranger,
            $this->resolver
        );

        $this->path_to_root = [
            (object)[
                'criteria' => ['type' => 'NP'],
                'relation_name' => 'building',
                'fields' => ['type']
            ],
            (object)[
                'criteria' => ['building' => ':building.id'],
                'relation_name' => '__root',
                'fields' => ['building']
            ]
        ];
        $this->relation_to_mapper = [
            '__root'   => ['mapper' => 'fakeRootMapper'],
            'building' => ['mapper' => 'fakeBuildingMapper']
        ];

        $this->row_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\RowMapperInterface');
        $this->aggregate_mapper->shouldReceive('getRelationToMapper')->once()->andReturn($this->relation_to_mapper);

        $this->query = \Mockery::mock('Aura\SqlMapper_Bundle\Query\AbstractConnectedQuery');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testInvokeToRoot()
    {
        $this->row_mapper->shouldReceive('getIdentityField')->times(3)->andReturn('id');
        $building_results = [
            (object)[
                'id' => 1,
                'type' => 'NP'
            ]
        ];
        $root_results = [
            (object)[
                'id' => 2,
                'building' => 1
            ]
        ];
        $expected = [
            '__root' => $root_results,
            'building' => $building_results
        ];
        $this
            ->resolver
            ->shouldReceive('resolve')
            ->once()
            ->with('NP', [], $this->aggregate_mapper)
            ->andReturn('NP');
        $this
            ->resolver
            ->shouldReceive('resolve')
            ->once()
            ->with(':building.id', ['building' => $building_results], $this->aggregate_mapper)
            ->andReturn(1);
        $this
            ->row_mapper
            ->shouldReceive('selectBy')
            ->once()
            ->with('type', 'NP', ['type', 'id'])
            ->andReturn($this->query);
        $this
            ->row_mapper
            ->shouldReceive('selectBy')
            ->once()
            ->with('building', 1, ['building', 'id'])
            ->andReturn($this->query);
        $this
            ->query
            ->shouldReceive('__toString')
            ->times(2)
            ->andReturn('BUILDING SELECT', '__ROOT SELECT');
        $this
            ->query
            ->shouldReceive('getBindValues')
            ->twice()
            ->andReturn(null, 1);
        $this
            ->row_mapper
            ->shouldReceive('getWriteConnection->fetchAll')
            ->once()
            ->with('BUILDING SELECT', null)
            ->andReturn($building_results);
        $this
            ->row_mapper
            ->shouldReceive('getWriteConnection->fetchAll')
            ->once()
            ->with('__ROOT SELECT', 1)
            ->andReturn($root_results);

        $this->assertEquals($expected, $this->callback->__invoke($this->path_to_root));
    }

    public function testInvokeOnlyRoot()
    {
        $this->row_mapper->shouldReceive('getIdentityField')->once()->andReturn('id');
        $path_to_root =  [
            (object)[
                'criteria' => null,
                'relation_name' => '__root',
                'fields' => ['building']
            ]
        ];
        $this
            ->row_mapper
            ->shouldReceive('select')
            ->once()
            ->with(['id'])
            ->andReturn($this->query);
        $this
            ->query
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('ROOT SELECT');
        $this
            ->query
            ->shouldReceive('getBindValues')
            ->once()
            ->andReturn(null);
        $this
            ->row_mapper
            ->shouldReceive('getWriteConnection->fetchAll')
            ->once()
            ->with('ROOT SELECT', null)
            ->andReturn('RESULTS!!!');

        $this->assertEquals(['__root' => 'RESULTS!!!'], $this->callback->__invoke($path_to_root));
    }

}
