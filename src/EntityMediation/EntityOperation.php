<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Relations\Relation;

/*
 * @todo inject the EntityMapper into here as well
 */
class EntityOperation
{
    /** @var string */
    protected $method;

    /** @var array */
    protected $fields = [];

    /** @var OperationCriteria */
    protected $criteria;

    /** @return string */
    public function getMethod()
    {
        return $this->method;
    }

    /** @param string $method */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /** @return array */
    public function getFields()
    {
        return $this->fields;
    }

    /** @param array $fields */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /** @return OperationCriteria */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /** @param OperationCriteria $criteria */
    public function setCriteria(OperationCriteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /** @return Relation */
    public function getRelation()
    {
        return $this->criteria->getRelation();
    }
}