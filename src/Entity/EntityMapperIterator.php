<?php
/**
 *
 * This file is part of the Aura project for PHP.
 *
 * @package Aura.SqlMapper_Bundle
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\SqlMapper_Bundle\Entity;

use Iterator;

/**
 *
 * An object to allow iteration over the instances in a RowMapperLocator.
 *
 * @package Aura.SqlMapper_Bundle
 *
 */
class EntityMapperIterator implements Iterator
{
    /**
     *
     * The mappers over which we are iterating.
     *
     * @var EntityMapperLocator
     *
     */
    protected $mapper_locator;

    /**
     *
     * The keys to iterate over in the RowMapperLocator object.
     *
     * @var array
     *
     */
    protected $keys;

    /**
     *
     * Is the current iterator position valid?
     *
     * @var bool
     *
     */
    protected $valid;

    /**
     *
     * Constructor.
     *
     * @param EntityMapperLocator $mapper_locator The RowMapperLocator object over which to iterate.
     *
     * @param array $keys The keys in the RowMapperLocator object.
     *
     */
    public function __construct(EntityMapperLocator $mapper_locator, array $keys = [])
    {
        $this->mapper_locator = $mapper_locator;
        $this->keys = $keys;
    }

    /**
     *
     * Returns the value at the current iterator position.
     *
     * @return EntityMapperInterface
     *
     */
    public function current()
    {
        return $this->mapper_locator->__get($this->key());
    }

    /**
     *
     * Returns the current iterator position.
     *
     * @return string
     *
     */
    public function key()
    {
        return current($this->keys);
    }

    /**
     *
     * Moves the iterator to the next position.
     *
     * @return null
     *
     */
    public function next()
    {
        $this->valid = (next($this->keys) !== false);
    }

    /**
     *
     * Moves the iterator to the first position.
     *
     * @return null
     *
     */
    public function rewind()
    {
        $this->valid = (reset($this->keys) !== false);
    }

    /**
     *
     * Is the current iterator position valid?
     *
     * @return null
     *
     */
    public function valid()
    {
        return $this->valid;
    }
}
