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

    /**
     *
     * Returns the map of property address to relation info for properties that map directly to a relation.
     *
     * @return array
     *
     */
    public function getRelationMap();

    /**
     *
     * Returns a merged version of the property map and the relation map that represents every group of properties
     * by their relation name and provides the mapper they are associated with, the fields they represent, and all
     * of the relations (parent AND child) that are visible from this address.
     *
     * @return array
     *
     */
    public function getRelationToMapper();

    /**
     *
     * Create a collection from an array of rows.
     *
     * @param array $data An array of rows.
     *
     * @return mixed
     *
     */
    public function newCollection($data);

    /**
     *
     * Create an AggregateDomain object from a single row.
     *
     * @param mixed $data A single row.
     *
     * @return mixed
     *
     */
    public function newObject($data);

    /**
     *
     * Looks up the mapper.field declaration by property address
     *
     * @param string $property_address The property key to look up on.
     *
     * @return string|null The mapper.field declaration.
     *
     */
    public function lookUpProperty($property_address);

    /**
     *
     * Get the relation declaration by name.
     *
     * @param string $relation_name The relation key to look up on.
     *
     * @return array|null Relation info.
     *
     */
    public function lookUpRelation($relation_name);

    /**
     *
     * Get all of the relations visible from the provided relation.
     *
     * @param string $relation_name The relation key to look up on.
     *
     * @return array|null Relation names.
     *
     */
    public function lookUpAllRelations($relation_name);

    /**
     *
     * Get the mapper for the provided relation name.
     *
     * @param string $relation_name The relation key to look up on.
     *
     * @return string|null The mapper for this particular relation.
     *
     */
    public function lookUpMapper($relation_name);

    /**
     *
     * Get the fields at the provided relation name.
     *
     * @param string $relation_name The relation key to look up on.
     *
     * @return array|null the field => property array at the given location.
     *
     */
    public function lookUpFields($relation_name);

    /**
     *
     * Separates a mapper address into a mapper name and a field name.
     *
     * @param string $mapper_address The address to separate.
     *
     * @return \stdClass An object with a mapper and field property.
     *
     */
    public function separateMapperFromField($mapper_address);

    /**
     *
     * Separates a property address into an address and property name.
     *
     * @param string $property_address The address to separate.
     *
     * @param bool $include_root_address If true, then any single segment property addresses will have the root
     * address alias as your address property. Otherwise the address property will just equal an empty string.
     *
     * @return \stdClass An object with an address property and a property property.
     *
     */
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


    /**
     *
     * Returns an array of all the mapper names used by this aggregate
     *
     * @return array
     *
     */
    public function getMapperNames();
}
