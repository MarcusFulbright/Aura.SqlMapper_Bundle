<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Factories;

use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\User;

class UserFactory implements ObjectFactoryInterface
{
    public function newObject(array $row)
    {
        $user = new User();
        $user->setBuilding($row['building']);
        $user->setFloor($row['floor']);
        $user->setId($row['id']);
        $user->setName($row['name']);
        return $user;
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