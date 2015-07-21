<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\BuildingType;

class BuildingTypeFactory implements ObjectFactoryInterface
{
    public function newObject(array $row)
    {
        $buildingType = new BuildingType();
        $buildingType->setId($row['id']);
        $buildingType->setCode($row['code']);
        $buildingType->setDecode($row['decode']);
        return $buildingType;
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