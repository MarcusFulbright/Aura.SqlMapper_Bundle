<?php
namespace Aura\SqlMapper_Bundle;

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

    /**
     *
     * Glues together the provided pieces using the appropriate delimiter for a property address
     *
     * @param mixed $pieces
     *
     * @return string
     *
     */
    public function joinAddress($pieces);

    /**
     *
     * Used to get the persist order, returns null if order is not set.
     *
     * @return array|null
     *
     */
    public function getPersistOrder();

    /**
     *
     * Sets the persist order
     *
     * @param array $order
     *
     * @return void
     *
     */
    public function setPersistOrder(array $order);
}
