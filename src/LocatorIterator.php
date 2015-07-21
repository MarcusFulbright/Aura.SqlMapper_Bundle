<?php
namespace Aura\SqlMapper_Bundle;

class LocatorIterator implements LocatorIteratorInterface, \Iterator
{
    /**
     *
     * The mappers over which we are iterating.
     *
     * @var LocatorInterface
     *
     */
    protected $locator;

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
     * @param LocatorInterface $locator.
     *
     * @param array $keys The keys in the RowMapperLocator object.
     *
     */
    public function __construct(LocatorInterface $locator, array $keys = [])
    {
        $this->locator = $locator;
        $this->keys = $keys;
    }

    /**
     *
     * Returns the value at the current iterator position.
     *
     * @return mixed
     *
     */
    public function current()
    {
        return $this->locator->__get($this->key());
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
     * @return void
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
     * @return void
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
     * @return bool
     *
     */
    public function valid()
    {
        return $this->valid;
    }
}