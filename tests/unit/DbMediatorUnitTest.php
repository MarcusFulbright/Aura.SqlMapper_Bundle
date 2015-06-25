<?php
namespace Aura\SqlMapper_Bundle;

use Mockery\MockInterface;

class DbMediatorUnitTest extends \PHPUnit_Framework_TestCase
{
    /** @var MapperLocator */
    protected $locator;

    /** @var MockInterface */
    protected $arranger;

    /** @var MockInterface */
    protected $resolver;

    /** @var MockInterface */
    protected $extractor;

    /** @var MockInterface */
    protected $callback_factory;

    /** @var MockInterface */
    protected $row_mapper;

    /** @var MockInterface */
    protected $aggregate_mapper;

    /** @var array */
    protected $path_to_root;

    /** @var array */
    protected $path_from_root;

    /** @var DbMediator */
    protected $mediator;

    /** @var array */
    protected $criteria;

    /** @var array */
    protected $relation_to_mapper;

    /** @var MockInterface */
    protected $transaction;

    /** @var \stdClass */
    protected $obj;

    /** @var array */
    protected $extracted;

    /** @var MockInterface */
    protected $context;

    /** @var MockInterface */
    protected $commit_callback;

    /** @var array */
    protected $operation_list;

    public function setUp()
    {
        $this->row_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AbstractMapper');

        $factories = [
            'fakeRootMapper' => function() {return $this->row_mapper;},
            'fakeBuildingMapper' => function() {return $this->row_mapper;}
        ];
        $this->locator  = new MapperLocator($factories);
        $this->arranger = \Mockery::mock('Aura\SqlMapper_Bundle\OperationArranger');
        $this->resolver = \Mockery::Mock('Aura\SqlMapper_Bundle\PlaceholderResolver');
        $this->extractor = \Mockery::mock('Aura\SqlMapper_Bundle\RowDataExtractor');
        $this->callback_factory = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\CallbackFactoryInterface');
        $this->aggregate_mapper = \Mockery::mock('Aura\SqlMapper_Bundle\AggregateMapperInterface');
        $this->transaction = \Mockery::mock('Aura\SqlMapper_Bundle\Transaction');
        $this->context = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\OperationContext');
        $this->commit_callback = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\CommitCallback');
        $this->operation_list = [$this->context, $this->context];
        $this->obj = (object) [
            'id' => 2,
            'name' => 'new __root',
            'building' => (object) [
                'id' => 3,
                'name' => 'New Building Name',
                'type' => 'NP'
            ]
        ];
        $this->extracted = [
            '__root' => [
                (object)[
                    'row_data' => (object)[
                        'id' => 2,
                        'name' => 'new __root',
                        'building' => 3
                    ],
                    'instance' => $this->obj
                ]
            ],
            'building' => [
                (object)[
                    'row_data' => (object)[
                        'id' => 3,
                        'name' => 'New Building Name',
                        'type' => 'NP'
                    ],
                    'instance' => $this->obj->building
                ]
            ]
        ];
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
            '__root'   => ['mapper' => 'fakeRootMapper', 'fields' => ['id' => 'id']],
            'building' => ['mapper' => 'fakeBuildingMapper', 'fields' => ['id' => 'id']]
        ];
        $this->aggregate_mapper->shouldReceive('getRelationToMapper')->andReturn($this->relation_to_mapper);
        $this->criteria = ['building.type' => 'NP'];

        $this->mediator = new DbMediator(
            $this->locator,
            $this->arranger,
            $this->resolver,
            $this->extractor,
            $this->callback_factory
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    protected function handleOperationList($callback)
    {
        $this
            ->row_mapper
            ->shouldReceive('getRowCache')
            ->twice()
            ->andReturnNull();
        $this
            ->callback_factory
            ->shouldReceive('newContext')
            ->once()
            ->with($this->extracted['__root'][0]->row_data, 'fakeRootMapper', '__root', null)
            ->andReturn($this->context);
        $this
            ->callback_factory
            ->shouldReceive('newContext')
            ->once()
            ->with($this->extracted['building'][0]->row_data, 'fakeBuildingMapper', 'building', null)
            ->andReturn($this->context);
        $callback
            ->shouldReceive('__invoke')
            ->times(2)
            ->with($this->context)
            ->andReturn($this->context);
    }

    public function handleUpdatePrimary()
    {
        $this
            ->row_mapper
            ->shouldReceive('isAutoPrimary')
            ->times(2)
            ->andReturn(true);
        $this
            ->row_mapper
            ->shouldReceive('getIdentityField')
            ->andReturn('id');
        $this
            ->row_mapper
            ->shouldReceive('getIdentityValue')
            ->with($this->extracted['__root'][0]->row_data)
            ->andReturn('Auto generated root PK');
        $this
            ->row_mapper
            ->shouldReceive('getIdentityValue')
            ->once()
            ->with($this->extracted['building'][0]->row_data)
            ->andReturn('Auto generated building PK');
    }

    protected function handleTransactionCommit()
    {
        $this
            ->callback_factory
            ->shouldReceive('getTransaction')
            ->once()
            ->andReturn($this->transaction);

        $this
            ->callback_factory
            ->shouldReceive('getCommitCallback')
            ->once()
            ->with($this->operation_list, $this->resolver, $this->locator, $this->extracted)
            ->andReturn($this->commit_callback);
        $this
            ->transaction
            ->shouldReceive('__invoke')
            ->once()
            ->with($this->commit_callback, $this->locator);
    }

    protected function handlePersistOrder()
    {
        $this
            ->aggregate_mapper
            ->shouldReceive('getPersistOrder')
            ->once()
            ->andReturn($this->path_from_root);
        $this
            ->extractor
            ->shouldReceive('getRowData')
            ->once()
            ->with($this->obj, $this->aggregate_mapper)
            ->andReturn($this->extracted);
    }

    protected function handleGetCallback($method, $callback)
    {
        $this
            ->callback_factory
            ->shouldReceive($method)
            ->once()
            ->andReturn($callback);
    }

    public function testSelect()
    {
        $select_id_callback = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\SelectIdentifierCallback');
        $select_callback = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\SelectCallback');
        $ids = ['__root' => [['id' => 1]], 'building' => [['id' => 2]]];

        $this
            ->arranger
            ->shouldReceive('getPathToRoot')
            ->once()
            ->with($this->aggregate_mapper, $this->criteria)
            ->andReturn($this->path_to_root);
        $this
            ->callback_factory
            ->shouldReceive('getIdentifierCallback')
            ->with($this->aggregate_mapper, $this->locator, $this->arranger, $this->resolver)
            ->andReturn($select_id_callback);
        $select_id_callback
            ->shouldReceive('__invoke')
            ->once()
            ->with($this->path_to_root)
            ->andReturn($ids);
        $this
            ->row_mapper
            ->shouldReceive('getIdentityField')
            ->andReturn('id');
        $this
            ->arranger
            ->shouldReceive('getPathFromRoot')
            ->once()
            ->with($this->aggregate_mapper, ['__root.id' => [1]])
            ->andReturn($this->path_from_root);
        $this
            ->callback_factory
            ->shouldReceive('getSelectCallback')
            ->with($this->aggregate_mapper, $this->locator, $this->arranger, $this->resolver)
            ->andReturn($select_callback);
        $select_callback
            ->shouldReceive('__invoke')
            ->once()
            ->with($this->path_from_root)
            ->andReturn('RESULTS!');
        $this->assertEquals('RESULTS!', $this->mediator->select($this->aggregate_mapper, $this->criteria));
    }

    public function testCreate()
    {
        $insert_callback = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\InsertCallback');
        $this->handlePersistOrder();
        $this->handleGetCallback('getInsertCallback', $insert_callback);
        $this->handleOperationList($insert_callback);
        $this->handleTransactionCommit();
        $this->handleUpdatePrimary();

        $results = $this->mediator->create($this->aggregate_mapper, $this->obj);
        $this->assertEquals('Auto generated root PK', $results->id);
        $this->assertEquals('Auto generated building PK', $results->building->id);
    }

    public function testUpdate()
    {
        $update_callback = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\UpdateCallback');
        $this->handlePersistOrder();
        $this->handleGetCallback('getUpdateCallback', $update_callback);
        $this->handleOperationList($update_callback);
        $this->handleTransactionCommit();
        $this->handleUpdatePrimary();

        $results = $this->mediator->update($this->aggregate_mapper, $this->obj);
        $this->assertEquals('Auto generated root PK', $results->id);
        $this->assertEquals('Auto generated building PK', $results->building->id);
    }

    public function testDelete()
    {
        $delete_callback = \Mockery::mock('Aura\SqlMapper_Bundle\OperationCallbacks\DeleteCallback');
        $this->handlePersistOrder();
        $this->handleGetCallback('getDeleteCallback', $delete_callback);
        $this->handleOperationList($delete_callback);
        $this->handleTransactionCommit();

        $this->assertTrue($this->mediator->delete($this->aggregate_mapper, $this->obj));
    }
}
