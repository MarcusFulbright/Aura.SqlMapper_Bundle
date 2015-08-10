<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

<<<<<<< HEAD
class EntityOperation
{
    /** @var string */
    protected $entity_name;

    /** @var string */
    protected $method = null;

    /** @var object */
    protected $instance;

    /** @var array */
    protected $criteria;

    public function __construct($entity_name, $instance, array $criteria = [])
    {
        $this->entity_name = $entity_name;
        $this->instance = $instance;
        $this->criteria = $criteria;
    }

    /** @return string */
    public function getEntityName()
    {
        return $this->entity_name;
    }

    /** @param string $entity_name */
    public function setEntityName($entity_name)
    {
        $this->entity_name = $entity_name;
    }
=======
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
>>>>>>> 5fa0775e710b72959ceb4ecd770cbca2d0945f8e

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

<<<<<<< HEAD
    /** @return object */
    public function getInstance()
    {
        return $this->instance;
    }

    /** @param object $instance */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /** @return array */
=======
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
>>>>>>> 5fa0775e710b72959ceb4ecd770cbca2d0945f8e
    public function getCriteria()
    {
        return $this->criteria;
    }

<<<<<<< HEAD
    /** @param array $criteria */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }
=======
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
>>>>>>> 5fa0775e710b72959ceb4ecd770cbca2d0945f8e
}