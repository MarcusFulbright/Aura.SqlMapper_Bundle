<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @package Aura.SqlMapper_Bundle
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\SqlMapper_Bundle;

/**
 *
 * Abstract Mapper class that keeps track of the property map and the relation map.
 *
 * Property maps are key value pairs of property addresses to the table.column definition.
 * Relation maps are key value pairs of a property address to an array of join information.
 *
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     *
     * Should Return an array that pairs addresses to columns.
     *
     * Example:
     *
     * array(
     *     'id'                         => 'account.accountId',
     *     'phones.id'                  => 'phones.phoneId',
     *     'phones.phoneNumber'         => 'table.column',
     *     'phones.accountId'           => 'phones.accountId'
     *     'phones.phoneref.description => 'phoneref.description'
     *     'phones.phoneref.id'         => 'phoneref.phonerefId',
     *     'phones.phoeref.phoneId      => 'phoneref.phoneId',
     * );
     *
     * @return mixed
     *
     */
    abstract public function getPropertyMap();

    /**
     * array(
     *   'email' => array(
     *       'EmailAddress',
     *       'EmailType',
     *       'EmailID'
     *   )
     * )
     *
     * @return null
     *
     */
    public function getEmbeddedMap()
    {
        return null;
    }


    /**
     *
     * Returns the name of the property that is a unique identifier(such as a primary key or UUID).
     *
     * @return string
     *
     */
    abstract public function getIdentityProp();

    /**
     *
     * Should return a map of all relationship info or null of there are no relations.
     *
     * array(
     *    'phones' => array(
     *        'identity_prop' => 'phones.id',
     *        'join_prop'     => 'phones.accountId',
     *        'joins_to'      => 'id',
     *        'owner'         => true
     *    ),
     *    'phones.PhoneRef'   => array(
     *        'identity_prop' => 'phones.phoneref.id'
     *        'join_prop'     => 'phones.phoneref.phoneId'
     *        'joins_to'      => 'phones.id
     *        'owner'         => true
     *    ),
     *    'address' => array(
     *        'identity_prop' => 'addressID',
     *        'join_prop'     => 'addressID',
     *        'joins_to'      => 'accountAddressID'
     *        'owner'         => false
     *    ),
     *    'email' => array(
     *        'identity_prop' => 'emailID'
     *        'join_prop'     => 'emailID'
     *        'joins_to'      => 'AccountEmailID'
     *        'owner'         => false
     *    )
     * )
     *
     * @param null $address Specific key to fetch information for.
     *
     * @return null|array
     *
     */
    public function getRelationMap($address = null)
    {
        return null;
    }

    /**
     *
     * Takes a random property address and returns the property info, or the relation info.
     *
     * @param string $address address to check for
     *
     * @return \stdClass|bool
     *
     * @throws \Exception When address is invalid
     *
     */
    public function resolveAddress($address)
    {
        if ($this->mapsToCol($address)) {
            $property_map = $this->getPropertyMap();
            return $this->getTableAndColumn($property_map[$address]);
        }

        if ($this->mapsToRelation($address)) {
            $relation_map = $this->getRelationMap();
            return $this->getTableAndColumn($relation_map[$address]);
        }

        /**
         * @todo throw a better exception with a message
         */
        throw new \Exception();
    }

    /**
     *
     * Check to see if a given address is a key in the property map.
     *
     * @param string $address address to check for
     *
     * @return bool
     *
     */
    public function mapsToCol($address)
    {
        $property_map = $this->getPropertyMap();
        return isset($property_map[$address]);
    }

    /**
     *
     * Check to see if a given address is a key in the relation map.
     *
     * @param string $address address to check for
     *
     * @return bool
     *
     */
    public function mapsToRelation($address)
    {
        $relation_map = $this->getRelationMap();
        return isset($relation_map[$address]);
    }

    /**
     *
     * Handles splitting up a table.column from the property map.
     *
     * @param $string
     *
     * @return \stdClass
     *
     */
    protected function getTableAndColumn($string)
    {
        $exploded =  explode('.', $string);
        $output   = new \stdClass();
        $output->column = array_pop($exploded);
        $output->table  = implode('.', $exploded);
        return $output;
    }
}
