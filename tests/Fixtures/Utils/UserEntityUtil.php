<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Utils;

use Aura\SqlMapper_Bundle\Tests\Fixtures\Entities\User;

/**
 * Trait UserEntityUtil
 * @package Aura\SqlMapper_Bundle\Tests\Fixtures\Utils
 */
trait UserEntityUtil 
{
    protected function newUser($id, $name, $building, $floor)
    {
        $user = new User();
        $user->setId($id);
        $user->setName($name);
        $user->setBuilding($building);
        $user->setFloor($floor);
        return $user;
    }

    protected function getAnna()
    {
        return $this->newUser('1', 'Anna', '1', '1');
    }

    protected function getBetty()
    {
        return $this->newUser('2','Betty','1','2');
    }

    protected function getClara()
    {
        return $this->newUser('3', 'Clara', '1', '3');
    }

    protected function getDonna()
    {
        return $this->newUser('4', 'Donna', '1', '1');
    }

    protected function getEdna()
    {
        return $this->newUser('5', 'Edna', '1', '2');
    }

    protected function getFiona()
    {
        return $this->newUser('6', 'Fiona', '1', '3');
    }
}