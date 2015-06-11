<?php
namespace Aura\SqlMapper_Bundle;


use Aura\SqlQuery\Common\Select;

interface AggregateMapperInterface
{
    /**
     *
     * Returns the map of property addresses to mapper.field names.
     *
     * @return array
     *
     */
    public function getPropertyMap();
    public function getRelationMap();
    public function getRelationToMapper();

    public function newCollection($data);
    public function newObject($data);

    public function lookUpRelation($relation_name);
    public function lookUpProperty($property_address);
    public function lookUpAllRelations($relation_name);
    public function lookUpMapper($relation_name);

    public function separateMapperFromField($mapper_address);
    public function separatePropertyFromAddress($property_address, $include_root_address = true);
}
