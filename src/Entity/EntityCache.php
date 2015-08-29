<?php
namespace Aura\SqlMapper_Bundle\Entity;

/**
 * Class EntityCache
 *
 * Responsible for holding last-known information on rows, so when we create new queries,
 * we can compare data with the information in here to determine which fields need updated
 * and which rows need touched.
 *
 *
 */
class EntityCache implements EntityCacheInterface
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
        $this->identity     =($identity);
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
        $results = $this->queryCache([$this->identity => $id])->results;
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
        return $this->get($this->getIdentity($row));
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
     * @param object $row The row to remove from the cache.
     *
     * @return null|object The row if there was a cached version, else null.
     *
     * @throws \Exception If provided row does not have the appropriate identity field.
     *
     */
    public function removeCachedVersion($row)
    {
        if ($cached = $this->queryCacheInstances([$this->identity => $this->getIdentity($row)], false)->results) {
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
     * @param array $criteria set  of key values used for where clause
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
    public function queryCache(array $criteria)
    {
        return $this->queryCacheInstances($criteria);
    }

    /**
     *
     * Query the cache for the provided field and value matches, and return the matching
     * rows. Note, this is protected so we can use this to pull back the instances of those
     * rows.
     *
     *
     * @param array $criteria set of key values used for where clause
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
    protected function queryCacheInstances(array $criteria, $clone = true)
    {
        $output = new \StdClass();
        $output->results = array();
        $output->ids = array();
        foreach ($this->cache as $row) {
            if (! $this->isAlive($row)) {
                $this->cache->detach($row);
            } elseif (
                $this->rowMatchesCriteria($row, $criteria)
                && !in_array($this->getIdentity($row), $output->ids)
            ) {
                $output->results[] = $clone ? clone $row : $row;
                $output->ids[] = $this->getIdentity($row);
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
     * @param array $criteria set of key values to use for comparison
     *
     * @return bool Whether or not it matches.
     *
     */
    protected function rowMatchesCriteria($row, array $criteria)
    {
        $output = true;
        array_walk(
            $criteria,
            function ($value, $field) use ($row, &$output) {
                $value = is_array($value) ? $value : array($value);
                foreach ($value as $match) {
                    if (property_exists($row, $field) && $this->getFieldValue($row, $field) == $match) {
                        continue;
                    } else {
                        $output = false;
                        break;
                    }
                }
            }
        );
        return $output;
    }

    /**
     *
     * Uses Reflection to get the identity field from the given object.
     *
     * @param object $obj
     *
     * @return mixed
     *
     */
    protected function getIdentity($obj)
    {
        return $this->getFieldValue($obj, $this->identity);
    }

    /**
     *
     * Uses reflection to get the value of the given for the given object.
     *
     * @param $obj
     *
     * @param $field
     *
     * @return mixed
     *
     */
    protected function getFieldValue($obj, $field)
    {
        $refl = new \ReflectionObject($obj);
        $prop = $refl->getProperty($field);
        $prop->setAccessible(true);
        return $prop->getValue($obj);
    }

}