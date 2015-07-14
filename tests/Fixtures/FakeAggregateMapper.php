<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Aggregate\AbstractAggregateMapper;

class FakeAggregateMapper extends AbstractAggregateMapper
{
    protected $property_map = [
        'id'                      => 'aura_test_table.id',
        'name'                    => 'aura_test_table.name',
        'building.id'             => 'aura_test_building.id',
        'building.name'           => 'aura_test_building.name',
        'building.type.id'        => 'aura_test_building_typeref.id',
        'building.type.code'      => 'aura_test_building_typeref.code',
        'building.type.decode'    => 'aura_test_building_typeref.decode',
        'floor.id'                => 'aura_test_floor.id',
        'floor.name'              => 'aura_test_floor.name',
        'task.id'                 => 'aura_test_task.id',
        'task.name'               => 'aura_test_task.name',
        'task.type.code'          => 'aura_test_task_typeref.code',
        'task.type.decode'        => 'aura_test_task_typeref.decode',
        'task.type.id'            => 'aura_test_task_typeref.id'
    ];

    protected $relation_map = [
        'building' => [
            'join_property'   => 'id',
            'reference_field' => 'aura_test_table.building',
            'owner'           => false,
            'type'            => 'hasOne'
        ],
        'building.type' => [
            'join_property'   => 'code',
            'reference_field' => 'aura_test_building.type',
            'owner'           => false,
            'type'            => 'hasOne'
        ],
        'floor' => [
            'join_property'   => 'id',
            'reference_field' => 'aura_test_table.floor',
            'owner'           => false,
            'type'            => 'hasOne'
        ],
        'task' => [
            'join_property'   => 'id',
            'reference_field' => 'aura_test_task.userid',
            'owner'           => true,
            'type'            => 'hasMany'
        ],
        'task.type' => [
            'join_property'   => 'code',
            'reference_field' => 'aura_test_task.type',
            'owner'           => false,
            'type'            => 'hasOne'
        ]
    ];

    /**
     * For testing purposes, this class is configurable. By calling includeRelation() with the
     * property name, we can remove properties from the omit array, which will include them in
     * the output of getPropertyMap and getRelationMap.
     *
     * @var array
     */
    protected $omit = [
        'building',
        'building.type',
        'floor',
        'task',
        'task.type'
    ];


    /**
     * Returns a property map, filtering out all of the addresses in the $omit array.
     *
     * @return array
     */
    public function getPropertyMap()
    {
        $omit = $this->omit;
        $filtered_keys = array_filter(
            array_keys($this->property_map),
            function ($key) use($omit) {
                foreach ($omit as $address) {
                    if (strpos($key, "$address.") === 0) {
                        return false;
                    }
                }
                return true;
            }
        );
        return array_intersect_key($this->property_map, array_flip($filtered_keys));
    }

    /**
     * Returns the relation map, filtering out all of the addresses in the $omit array.
     *
     * @return array
     */
    public function getRelationMap()
    {
        $omit = $this->omit;
        $filtered_keys = array_filter(
            array_keys($this->relation_map),
            function ($key) use($omit) {
                foreach ($omit as $address) {
                    if ($key === $address) {
                        return false;
                    }
                }
                return true;
            }
        );
        return array_intersect_key($this->relation_map, array_flip($filtered_keys));
    }

    /**
     * This will trigger included relations in the map output. This is only for testing purposes
     * and should never be implemented this way.
     *
     * @param string ...$address Addresses to be included
     *
     * @return bool
     */
    public function includeRelation() {
        $args = func_get_args();
        foreach ($args as $address) {
            if (in_array($address, $this->omit) !== false) {
                $index = array_search($address, $this->omit);
                array_splice(
                    $this->omit,
                    $index,
                    1
                );
            }
        }
    }

    /**
     * This will trigger included relations in the map output. This is only for testing purposes
     * and should never be implemented this way.
     *
     * @param string ...$address Addresses to be omitted.
     *
     * @return bool
     */
    public function excludeRelation() {
        $args = func_get_args();
        foreach ($args as $address) {
            if (in_array($address, $this->omit) === false) {
                $this->omit[] = $address;
            }
        }
    }
}