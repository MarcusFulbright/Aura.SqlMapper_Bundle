<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Relations\Relation;
use Aura\SqlMapper_Bundle\Relations\RelationLocator;

class RelationGenerator
{
    public function getRelationLocator(array $relations)
    {
        $output = [];
        foreach ($relations as $relation) {
            $method = 'get_'.$relation;
            $output = array_merge($output, $this->$method());
        }
        return new RelationLocator($output);
    }

    public function get_building_to_type()
    {
        return [
            'building_to_type' => function() {
                return new Relation('building_entity', 'type', 'building_type_entity', 'code', Relation::HAS_ONE);
            }
        ];
    }

    public function get_task_to_type()
    {
        return [
            'task_to_type' => function () {
                return new Relation('task_entity', 'type', 'task_type_entity', 'code', Relation::HAS_ONE);
            }
        ];
    }

    public function get_user_to_floor()
    {
        return [
            'user_to_floor' => function() {
                return new Relation('user_entity', 'floor', 'floor_entity', 'id', Relation::HAS_ONE);
            }
        ];
    }

    public function get_task_aggregate_to_user()
    {
        return [
            'task_aggregate_to_user' => function() {
                return new Relation('task_aggregate', 'userid', 'user_entity', 'id', Relation::HAS_MANY);
            }
        ];
    }

    public function get_user_to_building_aggregate()
    {
        return [
            'user_to_building_aggregate' => function() {
                return new Relation('user_entity', 'building', 'building_aggregate', 'id', Relation::HAS_ONE);
            }
        ];
    }
}