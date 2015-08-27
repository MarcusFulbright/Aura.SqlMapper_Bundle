<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\Tests\Fixtures\Aggregates\BuildingAggregate;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Building;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\BuildingType;

class BuildingAggregateFactory
{
    public function newObject(Building $building, BuildingType $building_type)
    {
        return new BuildingAggregate($building, $building_type);
    }

    public function newCollection(array $rows)
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->newObject($row['building'], $row['type']);
        }
        return $collection;
    }
}