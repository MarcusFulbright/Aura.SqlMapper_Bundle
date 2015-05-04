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
 * Interface for mapper objects.
 *
 * @package Aura.SqlMapper_Bundle
 *
 */
interface MapperInterface
{
    /**
     * @return array
     */
    public function getPropertyMap();

    /**
     * @return array
     */
    public function getRelationMap();

    /**
     * @param $address
     * @return string|array
     */
    public function resolveAddress($address);

    /**
     * @param $address
     * @return bool
     */
    public function mapsToCol($address);

    /**
     * @param $address
     * @return bool
     */
    public function mapsToRelation($address);

    /**
     * @return string
     */
    public function getIdentityProp();

        /**
     *
     * Handles splitting up a table.column from the property map.
     *
     * @param $string
     *
     * @return \stdClass
     *
     */
    public function getTableAndColumn($string);
}
