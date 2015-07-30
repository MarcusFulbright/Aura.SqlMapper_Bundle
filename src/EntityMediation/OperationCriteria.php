<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Relations\Relation;

class OperationCriteria
{
    /** @var Relation */
    protected $relation;

    /** @var array */
    protected $criteria;

    /**
     *
     * Constructor.
     *
     * @param Relation $relation
     *
     * @param array{field => value} $criteria
     */
    public function __construct(Relation $relation, array $criteria)
    {
        $this->relation = $relation;
        $this->criteria = $criteria;
    }

    /** @return Relation */
    public function getRelation()
    {
        return $this->relation;
    }

    /** @param Relation $relation */
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }

    /** @return array */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /** @param array $criteria */
    public function setCriteria(array $criteria)
    {
        $this->criteria = $criteria;
    }

}