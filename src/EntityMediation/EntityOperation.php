<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

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
    public function getCriteria()
    {
        return $this->criteria;
    }

    /** @param array $criteria */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }
}