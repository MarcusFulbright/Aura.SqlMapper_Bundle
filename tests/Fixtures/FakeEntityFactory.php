<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;

class FakeEntityFactory implements ObjectFactoryInterface
{
    public function newObject(array $row = [])
    {
            return (object) $row;
    }

    public function newCollection(array $rows = [])
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->newObject($row);
        }
        return $collection;
    }
}