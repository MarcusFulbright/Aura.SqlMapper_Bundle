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
    protected $placeholders;

    public function __construct($entity_name, $instance, array $placeholders = [])
    {
        $this->entity_name = $entity_name;
        $this->instance = $instance;
        $this->placeholders = $placeholders;
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
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /** @param array $placeholders */
    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;
    }
}