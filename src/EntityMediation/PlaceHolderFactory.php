<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

class PlaceHolderFactory
{
    public function newPlaceHolder(callable $value)
    {
        return new PlaceHolder($value);
    }
}