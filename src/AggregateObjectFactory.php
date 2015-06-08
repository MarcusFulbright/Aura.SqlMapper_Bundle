<?php
namespace Aura\SqlMapper_Bundle;

class AggregateObjectFactory implements ObjectFactoryInterface
{

    private function isEmbeddedObject($property) {
        return (is_array($property) && (bool) count(array_filter(array_keys($property), 'is_string')));
    }

    public function newObject(array $data = array())
    {
        $object = (object) $data;
        foreach ($data as $property => $value) {
            if ($this->isEmbeddedObject($value)) {
                $object->property = $this->newObject($value);
            } elseif (is_array($value)) {
                $sub_array = array();
                foreach ($value as $sub_value) {
                     $sub_array[] = $this->newObject($sub_value);
                }
                $object->property = $sub_array;
            }
        }
        return $object;
    }

    public function newCollection(array $rows = array())
    {
        $coll = array();
        foreach ($rows as $row) {
            $coll[] = $this->newObject($row);
        }
        return $coll;
    }
}
