<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\Exception\InvalidLocatorMember;
use Aura\SqlMapper_Bundle\Exception\NoSuchMember;

abstract class AbstractLocator implements LocatorInterface
{
    /**
     *
     * A registry of callables to create member instances.
     *
     * @var array
     *
     */
    protected $members = [];

    /**
     *
     * A registry of object instances created by the members callables.
     *
     * @var array
     *
     */
    protected $instances = [];

    /**
     *
     * Constructor.
     *
     * @param array $members An array of key-value pairs where the key is a
     * name and the value is a callable that returns a mapper instance.
     *
     * @throws InvalidLocatorMember if one of the members is not callable
     *
     */
    public function __construct(array $members)
    {
        foreach ($members as $member) {
            if (! is_callable($member)) {
                throw new InvalidLocatorMember('All Locator Members must be callable');
            }
        }

        $this->members = $members;
    }

    /**
     *
     * IteratorAggregate: Returns an iterator for this locator.
     *
     * @return LocatorIterator
     *
     */
    public function getIterator()
    {
        return new LocatorIterator($this, array_keys($this->members));
    }

    /**
     *
     * Gets member instance by name; if it has not been created yet, its
     * callable factory will be invoked and the instance will be retained.
     *
     * @param string $key The name of the mapper instance to retrieve.
     *
     * @return mixed.
     *
     * @throws NoSuchMember when an unrecognized member key is given
     *
     */
    public function __get($key)
    {
        if (! isset($this->members[$key])) {
            throw new NoSuchMember($key);
        }

        if (! isset($this->instances[$key])) {
            $callable = $this->members[$key];
            $this->instances[$key] = $callable();
        }

        return $this->instances[$key];
    }
}