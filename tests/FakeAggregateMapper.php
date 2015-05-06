<?php
namespace Aura\SqlMapper_Bundle;

/**
 * [summary].
 *
 *[description]
 */
class FakeAggregateMapper extends AbstractAggregateMapper
{

    protected $property_map = [
        'id'                      => 'aura_test_table.id',
        'name'                    => 'aura_test_table.',
        'buildingID'              => 'aura_test_table.',
        'floorID'                 => 'aura_test_table.',
        'building.id'             => 'aura_test_building.id',
        'building.name'           => 'aura_test_building.name',
        'building.typeCode'       => 'aura_test_building.type',
        'building.type.id'        => 'aura_test_building_typeref.id',
        'building.type.code'      => 'aura_test_building_typeref.code',
        'building.type.decode'    => 'aura_test_building_typeref.decode',
        'floor.id'                => 'aura_test_floor.id',
        'floor.name'              => 'aura_test_floor.name',
        'task.id'                 => 'aura_test_task.id',
        'task.userID'             => 'aura_test_task.userid',
        'task.name'               => 'aura_test_task.name',
        'task.typeCode'           => 'aura_test_task.type',
        'task.type.code'          => 'aura_test_task_typeref.code',
        'task.type.decode'        => 'aura_test_task_typeref.decode'
    ];

    protected $relation_map = [
        'building' => [
            'joinProperty' => 'id',
            'references'   => 'buildingID',
            'owner'        => false,
            'type'         => 'hasOne'
        ],
        'building.type' => [
            'joinProperty' => 'code',
            'references'   => 'typeCode',
            'owner'        => false,
            'type'         => 'hasOne'
        ],
        'floor' => [
            'joinProperty' => 'id',
            'references'   => 'floorID',
            'owner'        => false,
            'type'         => 'hasOne'
        ],
        'task' => [
            'joinProperty' => 'userID',
            'references'   => 'id',
            'owner'        => true,
            'type'         => 'hasMany'
        ],
        'task.type' => [
            'joinProperty' => 'code',
            'references'   => 'typeCode',
            'owner'        => false,
            'type'         => 'hasOne'
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
        'building.typeref',
        'floor',
        'task',
        'task.typeref'
    ];


    /**
     * Returns a property map, filtering out all of the addresses in the $omit array.
     *
     * @return array
     */
    public function getPropertyMap()
    {
        $omit = $this->omit;
        return array_filter(
            $this->property_map,
            function ($key) use($omit) {
                foreach ($omit as $address) {
                    if (strpos($key, "$address.") === 0) {
                        return false;
                    }
                }
                return true;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Returns the relation map, filtering out all of the addresses in the $omit array.
     *
     * @return array
     */
    public function getRelationMap()
    {
        $omit = $this->omit;
        return array_filter(
            $this->property_map,
            function ($key) use($omit) {
                foreach ($omit as $address) {
                    if (strpos($key, $address) === 0) {
                        return false;
                    }
                }
                return true;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * This will trigger included relations in the map output. This is only for testing purposes
     * and should never be implemented this way.
     *
     * @param $address
     *
     * @return bool
     */
    public function includeRelation($address) {
        if (in_array($address, $this->omit) !== false) {
            $index = array_search($address, $this->omit);
            array_splice(
                $this->omit,
                $index,
                1
            );
            return true;
        }
        return false;
    }

    /**
     * This will trigger included relations in the map output. This is only for testing purposes
     * and should never be implemented this way.
     *
     * @param $address
     *
     * @return bool
     */
    public function excludeRelation($address) {
        if (in_array($address, $this->omit) === false) {
            $this->omit[] = $address;
            return true;
        }
        return false;
    }
}