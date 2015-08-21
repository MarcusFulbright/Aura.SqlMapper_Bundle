<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

class EntityOperationFactory
{
    public function newOperation($entity_name, $instance, $placeholders = [])
    {
        return new EntityOperation($entity_name, $instance, $placeholders);
    }
}