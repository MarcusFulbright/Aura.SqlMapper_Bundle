<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Building;

class BuildingFactory implements  ObjectFactoryInterface
{
    public function newObject(array $row)
    {
        $building = new Building();
        $building->setName($row['name']);
        $building->setId($row['id']);
        $building->setType($row['type']);
        return $building;
    }

    public function newCollection(array $rows)
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->newObject($row);
        }
        return $collection;
    }
}