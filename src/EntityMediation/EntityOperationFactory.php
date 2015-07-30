<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

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
    }
}