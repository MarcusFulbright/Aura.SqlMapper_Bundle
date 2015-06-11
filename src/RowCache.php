<?php
/**
 * Created by IntelliJ IDEA.
 * User: conlanc
 * Date: 5/13/2015
 * Time: 5:04 PM
 */

namespace Aura\SqlMapper_Bundle;

/**
 * Class RowCache
 *
 * Responsible for holding last-known information on rows, so when we create new queries,
 * we can compare data with the information in here to determine which fields need updated
 * and which rows need touched.
 *
 * @package Aura\SqlMapper_Bundle
 *
 */
class RowCache implements RowCacheInterface
{

    /**
     *
     * Cache-money.
     *
     * @var \SplObjectStorage
     *
     */
    protected $cache;

    /**
     *
     * The property-name that represents the primary key on this object.
     *
     * @var string
     *
     */
    protected $identity;

    /**
     *
     * The time, in seconds, cached records are valid.
     *
     * @var int
     *
     */
    protected $time_to_live;

    /**
     *
     * Constructor
     *
     * @param string $identity The property name, as a string, that represents the
     * primary identity.
     *
     * @param int $time_to_live The time, in seconds, records are valid. If zero, or less,
     * record will live forever.
     *
     */
    public function __construct($identity, $time_to_live = 0)
    {
        $this->cache        = new \SplObjectStorage();
        $this->setIdentity($identity);
        $this->time_to_live = $time_to_live;
    }
    /**
     *
     * Setter for identity field
     *
     * @param string $identity The property name, as a string, that represents the
     * primary identity.
     *
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    /**
     *
     * Returns a cached row-object by its primary-key value.
     *
     * @param mixed $id The value the primary key must match.
     *
     * @return mixed|null|object The object or null if not cached yet.
     *
     */
    public function get($id)
    {
        $results = $this->queryCache($this->identity, $id)->results;
        return $results ? $results[0] : null;
    }

    /**
     *
     * Adds an item to the cache. If there is an existing item with the same
     * primary-key, remove and replace.
     *
     * @param mixed|object $row The row object to cache.
     *
     * @throws \Exception If provided row does not have the appropriate identity field.
     *
     * @return bool
     *
     */
    public function set($row)
    {
        $this->removeCachedVersion($row);
        $this->cache->attach(clone $row, time());
        return true;
    }

    /**
     *
     * Takes an instance of a row and returns a copy of the cached instance.
     *
     * @param mixed|object $row The row to get from the cache.
     *
     * @return mixed|null|object The row if there is a cached version, else null.
     *
     * @throws \Exception If provided row does not have the appropriate identity field.
     *
     */
    public function getCachedData($row)
    {
        $this->validateRow($row);
        return $this->get($row->{$this->identity});
    }

    /**
     *
     * Takes an instance of a row and returns a copy of the cached instance.
     *
     * @param mixed|object $row The row to check for.
     *
     * @return bool Whether or not this row exists in the cache.
     *
     * @throws \Exception If provided row does not have the appropriate identity field.
     *
     */
    public function isCached($row)
    {
        return $this->getCachedData($row) !== null;
    }

    /**
     *
     * Takes an instance of a row and removes any version of that row from the cache.
     *
     * @param mixed|object $row The row to remove from the cache.
     *
     * @return mixed|null|object The row if there was a cached version, else null.
     *
     * @throws \Exception If provided row does not have the appropriate identity field.
     *
     */
    public function removeCachedVersion($row)
    {
        if ($cached = $this->queryCacheInstances($this->identity, $row->{$this->identity}, false)->results) {
            $this->cache->detach($cached[0]);
        }
        return $cached;
    }

    /**
     *
     * Ensures that the provided row object has a valid identity field.
     *
     * @param mixed $row The row to check.
     *
     * @throws \Exception If row does not have the appropriate identity field.
     *
     */
    protected function validateRow($row)
    {
        if (property_exists($row, $this->identity) === false) {
            /**@todo throw better exception.*/
            throw new \Exception (
                "Row does not contain identity field '{$this->identity}' necessary for this cache'."
            );
        }
    }


    /**
     *
     * Checks if the provided object has surpassed the time-to-live or not.
     *
     * @param object|mixed $row The row to check.
     *
     * @return bool Whether or not this object is still valid.
     *
     */
    protected function isAlive($row)
    {
        if ($this->time_to_live > 0 && time() >= $this->cache[$row] + $this->time_to_live) {
            return false;
        }
        return true;
    }

    /**
     *
     * Query the cache for the provided field. Returns clones of the rows.
     *
     * @param string $field The field to match on.
     *
     * @param mixed $value Either a value to match or an array of acceptable values.
     *
     * @return \StdClass {
     *
     *      @property array $results An array of matching cached rows.
     *
     *      @property array $ids An array of the matching rows' identities.
     *
     * }
     *
     */
    public function queryCache($field, $value)
    {
        return $this->queryCacheInstances($field, $value);
    }

    /**
     *
     * Query the cache for the provided field and value matches, and return the matching
     * rows. Note, this is protected so we can use this to pull back the instances of those
     * rows.
     *
     * @param string $field The field to match on.
     *
     * @param mixed $value Either a value to match or an array of acceptable values.
     *
     * @param bool $clone Whether or not to return a clone of cached rows or not.
     *
     * @return \StdClass {
     *
     *      @property array $results An array of matching cached rows.
     *
     *      @property array $ids An array of the matching rows' identities.
     *
     * }
     *
     */
    protected function queryCacheInstances($field, $value, $clone = true)
    {
        $output = new \StdClass();
        $output->results = array();
        $output->ids = array();

        foreach ($this->cache as $row) {
            if ($this->isAlive($row) === false) {
                $this->cache->detach($row);
            } elseif (
                $this->rowMatchesCriteria($row, $field, $value)
                && !in_array($row->{$this->identity}, $output->ids)
            ) {
                $output->results[] = $clone ? clone $row : $row;
                $output->ids[] = $row->{$this->identity};
            }
        }

        return $output;
    }

    /**
     *
     * Check that the provided row matches the provided criteria.
     *
     * @param mixed $row The row to check.
     *
     * @param string $field The field we want to match on.
     *
     * @param mixed $value Either a value to match or an array of acceptable values.
     *
     * @return bool Whethor or not it matches.
     *
     */
    protected function rowMatchesCriteria($row, $field, $value) {
        $value = is_array($value) ? $value : array($value);
        foreach ($value as $match) {
            if (property_exists($row, $field) && $row->$field == $match) {
                return true;
            }
        }
        return false;
    }

}