<?php
namespace Aura\SqlMapper_Bundle\Entity;

interface EntityCacheInterface
{

    /**
     *
     * Gets a specific row by identity from the cache.
     *
     * @param mixed $id The value to grab.
     *
     * @return mixed|null
     *
     */
    public function get($id);

    /**
     *
     * Saves a row to the cache
     *
     * @param $row
     *
     * @return bool
     *
     */
    public function set($row);

    /**
     *
     * Sets the field to use as an identity field. Will affect how you store and retrieve rows.
     *
     * @param string $field The name of the field to use.
     *
     * @return void
     *
     */
    public function setIdentity($field);

    /**
     *
     * Returns the cached version of this particular row (by looking it up by identity) or null if it is uncached.
     *
     * @param mixed $row The row to look up.
     *
     * @return mixed|null The cached version
     *
     */
    public function getCachedData($row);

    /**
     *
     * Check if the provided row has been cached or not.
     *
     * @param mixed $row The row to check for.
     *
     * @return bool Whether it is cached or not.
     *
     */
    public function isCached($row);

    /**
     *
     * Removes the cached version of the provided row (if any) from the cached.
     *
     * @param mixed $row The row to check for and remove.
     *
     */
    public function removeCachedVersion($row);

    /**
     *
     * Get rows from the cache that match the provided criteria.
     *
     * @param array $criteria set of key values to use for comparison
     *
     * @return \StdClass An object with a results property that is an array of rows, and an ids property which
     * is an array of ids.
     *
     */
    public function queryCache(array $criteria);
}