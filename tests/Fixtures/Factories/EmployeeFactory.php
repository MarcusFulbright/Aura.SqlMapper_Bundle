<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\Tests\Fixtures\Aggregates\BuildingAggregate;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Aggregates\EmployeeAggregate;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\Floor;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\User;

class EmployeeFactory
{
    public function newObject(User $user, BuildingAggregate $building, Floor $floor, array $tasks)
    {
        return new EmployeeAggregate($user, $floor, $building, $tasks);
    }

    public function newCollection(array $rows)
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->newObject($row['user'], $row['building'], $row['floor'], $row['tasks']);
        }
        return $collection;
    }
}