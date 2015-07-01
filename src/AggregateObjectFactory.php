<?php
namespace Aura\SqlMapper_Bundle;

class AggregateObjectFactory implements ObjectFactoryInterface
{

    private function isEmbeddedObject($property) {
        return (is_array($property) && (bool) count(array_filter(array_keys($property), 'is_string')));
    }

    public function newObject(array $data = array())
    {
        if ($this->isEmbeddedObject($data)) {
            $object = (object) $data;
            foreach ($data as $property_name => $property_value) {
                if(is_array($property_value)) {
                    $object->$property_name = $this->newObject($property_value);
                }
            }
        } else {
            $object = array();
            foreach ($data as $array_member) {
                 $object[] = $this->newObject($array_member);
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
