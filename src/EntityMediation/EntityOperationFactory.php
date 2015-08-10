<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

<<<<<<< HEAD
class EntityOperationFactory
{
    public function newOperation($entity_name, $instance, $criteria = [])
    {
        return new EntityOperation($entity_name, $instance, $criteria);
=======
use Aura\SqlMapper_Bundle\Relations\Relation;

class EntityOperationFactory
{
    public function newOperation()
    {
        return new EntityOperation();
    }

    /**
     *
     * @param Relation $relation
     *
     * @param Array $criteria
     *
     * @return OperationCriteria
     *
     */
    public function newCriteria(Relation $relation, $criteria)
    {
        return new OperationCriteria($relation, $criteria);
>>>>>>> 5fa0775e710b72959ceb4ecd770cbca2d0945f8e
    }
}