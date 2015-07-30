<?php
namespace Aura\SqlMapper_Bundle\Relations;

class Relation 
{
    const HAS_ONE = 'hasOne';

    const HAS_MANY = 'hasMany';

    /** @var string */
    protected $owning_entity;

    /** @var string */
    protected $owning_field;

    /** @var string */
    protected $inverse_entity;

    /** @var string */
    protected $inverse_field;

    /** @var  string */
    protected $type;

    /**
     *
     * Constructor.
     *
     * @param string $owning_entity The entity that represents the owning side fo the relationship
     *
     * @param string $owning_field Field on the owning entity that contains join information
     *
     * @param string $inverse_entity Entity that represents the inverse side of the relation
     *
     * @param string $inverse_field Field on the inverse entity that contains join information
     *
     * @param string $type Either HAS_ONE or HAS_MANY (use this classes constants)
     *
     */
    public function __construct($owning_entity, $owning_field, $inverse_entity, $inverse_field, $type)
    {
        $this->owning_entity = $owning_entity;
        $this->owning_field = $owning_field;
        $this->inverse_entity = $inverse_entity;
        $this->inverse_field = $inverse_field;
        $this->type = $type;
    }

    /** @return string */
    public function getOwningEntity()
    {
        return $this->owning_entity;
    }

    /** @param string $owning_entity */
    public function setOwningEntity($owning_entity)
    {
        $this->owning_entity = $owning_entity;
    }

    /** @return string */
    public function getOwningField()
    {
        return $this->owning_field;
    }

    /** @param string $owning_field */
    public function setOwningField($owning_field)
    {
        $this->owning_field = $owning_field;
    }

    /** @return string */
    public function getInverseEntity()
    {
        return $this->inverse_entity;
    }

    /** @param string $inverse_entity */
    public function setInverseEntity($inverse_entity)
    {
        $this->inverse_entity = $inverse_entity;
    }

    /** @return string */
    public function getInverseField()
    {
        return $this->inverse_field;
    }

    /** @param string $inverse_field */
    public function setInverseField($inverse_field)
    {
        $this->inverse_field = $inverse_field;
    }

    /** @return string */
    public function getType()
    {
        return $this->type;
    }

    /** @param string $type */
    public function setType($type)
    {
        $this->type = $type;
    }
}


