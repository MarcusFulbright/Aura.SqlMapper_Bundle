<?php
namespace Aura\SqlMapper_Bundle;

interface ObjectFactoryInterface
{
    public function newObject(array $row);

    public function newCollection(array $rows);
}
