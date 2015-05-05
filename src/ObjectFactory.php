<?php
namespace Aura\SqlMapper_Bundle;

class ObjectFactory implements ObjectFactoryInterface
{
    public function newObject(array $row = array(), array $map = null)
    {
        if ($map != null) {
            $row = array_intersect_key($row, $map);
        }
        return (object) $row;
    }

    public function newCollection(array $rows = array(), array $map = null)
    {
        $coll = array();
        foreach ($rows as $row) {
            $coll[] = $this->newObject($row, $map);
        }
        return $coll;
    }
}
