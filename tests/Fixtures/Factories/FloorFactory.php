<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Floor;

class FloorFactory implements ObjectFactoryInterface
{
    public function newObject(array $row)
    {
        $floor = new Floor();
        $floor->setId($row['id']);
        $floor->setName($row['name']);
        return $floor;
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