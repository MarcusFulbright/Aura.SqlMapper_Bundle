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
     * Returns the name of the property that is a unique identifier(such as a primary key or UUID).
     *
     * @return string
     *
     */
    abstract public function getIdentityProp();

    /**
     *
     * Should Return an array that pairs addresses to columns.
     *
     * Example:
     *
     * array(
     *     'id'                         => 'account.accountId',
     *     'accountEmailID'             => 'account.emailId'
     *     'phones.id'                  => 'phone.phoneId',
     *     'phones.phoneNumber'         => 'phone.number',
     *     'phones.accountId'           => 'phone.accountId'
     *     'phones.description          => 'phone.phoneRef.description'
     *     'phones.phonerefid'          => 'phone.phoneRef.phonerefId',
     *     'phones.phoneId              => 'phone.phoneRef.phoneId',
     *     'EmailAddress'               => 'email.address',
     *     'EmailID'                    => 'email.id',
     *     'addressAccountID'           => 'address.accountID',
     *     'AddressLineOne'             => 'address.line_one'
     * );
     *
     * @return mixed
     *
     */
    abstract public function getPropertyMap();


    /**
     *
     * array(
     *    '__root' => array(
     *         'id',
     *         'accountEmailID',
     *         'EmailAddress',
     *         'addressAccountID',
     *         'AddressLineOne'
     *    ),
     *    'phones' => array(
     *         'id',
     *         'phoneNumber',
     *         'accountId',
     *         'description',
     *         'phonerefid',
     *         'phoneId',
     *    )
     * )
     *
     * array(
     *   'account' => array(
     *        'id',
     *        'accountEmailId'
     *   )
     *   'phone' => array
     *
     *
     *
     *
     */
    protected function locationsArray()
    {
        $property_map = $this->getPropertyMap();
        $map = array();
        foreach ($property_map as $address => $table_col) {
            $exploded =  explode('.', $address);
            $property = array_pop($exploded);
            $room     = implode('.', $exploded);
            $map[$room][] = $property;
        }
    }

    /**
     *
     * Should return a map of all relationship info or null of there are no relations.
     *
     * array(
     *    'phones' => array(
     *        'identity_prop' => 'phones.id',
     *        'join_prop'     => 'phones.accountId',
     *        'joins_to'      => 'account.id',
     *        'owner'         => true
     *    ),
     *    'phoneRef'   => array(
     *        'identity_prop' => 'phones.phoneref.id'
     *        'join_prop'     => 'phones.phoneref.phoneId'
     *        'joins_to'      => 'phones.id
     *        'owner'         => true
     *    ),
     *    'address' => array(
     *        'identity_prop' => 'address.id',
     *        'join_prop'     => 'address.accountID',
     *        'joins_to'      => 'account.id'
     *        'owner'         => true
     *    ),
     *    'email' => array(
     *        'identity_prop' => 'email.id'
     *        'join_prop'     => 'email.id'
     *        'joins_to'      => 'account.EmailID'
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
     * @return string|array
     *
     * @throws \Exception When address is invalid
     *
     */
    public function resolveAddress($address)
    {
        if ($this->mapsToCol($address)) {
            $property_map = $this->getPropertyMap();
            return $property_map[$address];
        }

        if ($this->mapsToRelation($address)) {
            $relation_map = $this->getRelationMap();
            return $relation_map[$address];
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
    public function getTableAndColumn($string)
    {
        $exploded =  explode('.', $string);
        $output   = new \stdClass();
        $output->column = array_pop($exploded);
        $output->table  = implode('.', $exploded);
        return $output;
    }
}
