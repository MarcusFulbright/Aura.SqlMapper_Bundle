<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;

class TaskAggregateFactory implements ObjectFactoryInterface
{
    public function newObject(array $row)
    {
        die('trying to build a new employee');
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