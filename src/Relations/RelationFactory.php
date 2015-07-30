<?php
namespace Aura\SqlMapper_Bundle\Relations;

class RelationFactory
{
    /**
     *
     * @param string $name
     *
     * @param string $join_property
     *
     * @param string $reference_field
     *
     * @param bool $owner
     *
     * @return Relation
     *
     */
    public function createHasOne($name, $join_property, $reference_field, $owner)
    {
        return new Relation($name, $join_property, $reference_field, $owner, Relation::HAS_ONE);
    }

    /**
     *
     * @param string $name
     *
     * @param string $join_property
     *
     * @param string $reference_field
     *
     * @param bool $owner
     *
     * @return Relation
     *
     */
    public function createHasMany($name, $join_property, $reference_field, $owner)
    {
        return new Relation($name, $join_property, $reference_field, $owner, Relation::HAS_MANY);
    }
}