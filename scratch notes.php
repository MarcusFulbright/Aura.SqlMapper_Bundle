<?php

$aggregates = [
	'building_aggregate' => $building_aggregate_builder,
	'task_aggregate'     => $task_aggregate_builder,
	'employee_aggregate' => $employee_aggregate_builder
];

$aggregate_builder_locator = new AggregateLocator($aggregates);

$entities = [
	'building_entity',     => $building_mapper,
	'building_type_entity' => $building_type_mapper,
	'task_entity',         => $task_mapper,
	'task_type_entity'     => $task_type_mapper,
	'floor_entity'         => $floor_mapper,
	'user_entity'          => $user_mapper
];

$entity_locator = new EntityLocator($entities);

																												//@todo how to best describe 1:1, 1:M, M:1 relationships....
$relation_map = [
	'building_to_type'           => new Relation('building_entity', 'type'       , 'building_type_entity', 'code', Relation::HAS_ONE);
	'task_to_type'               => new Relation('task_entity'    , 'type'       , 'task_type_entity'    , 'code', Relation::HAS_ONE);
	'user_to_floor'              => new Relation('user_entity'    , 'floor'      ,'floor_entity'         , 'id'  , Relation::HAS_ONE);
	'task_aggregate_to_user'     => new Relation('task_aggregate' , 'userid'     , 'user_entity'         , 'id'  , Relation::BELONGS_TO_ONE);
	'user_to_building_aggregate' => new Relation('user_entity'    ,  'building'  , 'building_aggregate'  , 'id'  , Relation::HAS_ONE)
];

$relation_locator = new RelationLocator($relation_map);

class EmployeeAggregateBuilder
{

	public function getRoot()
	{
		'user'
	}

	public function getAggregates()
	{
		return [
			'building_aggregate',
			'task_aggregate'
		];
	}
	public function getEntities()
	{
		return [			
			'floor',
			'user'
		];
	}

	public function getRelations()
	{
		return [
			'user_to_floor',
			'task_aggregate_to_user',
			'user_to_building_aggregate',
		];
	}
}

class TaskAggregateBuilder
{
	public function getRelations()
	{
		return [
			'task_to_type';
		];
	}
}

class BuildingAggregateBuilder
{
	public function getRelations()
	{
		return [
			'building_to_type'
		];
	}
}
