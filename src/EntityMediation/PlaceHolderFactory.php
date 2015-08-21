<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

class PlaceHolderFactory
{
    public function newObjectPlaceHolder($object, $field)
    {
        $callable = function() use ($object, $field) {
            $refl = new \ReflectionObject($object);
            $prop = $refl->getProperty($field);
            $prop->setAccessible(true);
            return $prop->getValue($object);
        };
        return new PlaceHolder($callable);
    }

    public function newCollectionPlaceHolder(array $objects, $field)
    {
        $callable = function () use ($objects, $field) {
            $values = [];
            foreach ($objects as $object) {
                $refl = new \ReflectionObject($object);
                $prop = $refl->getProperty($field);
                $prop->setAccessible(true);
                $values[] = $prop->getValue($object);
            }
            return $values;
        };
        return new PlaceHolder($callable);
    }

    public function newPlaceHolder(callable $value)
    {
        return new PlaceHolder($value);
    }
}