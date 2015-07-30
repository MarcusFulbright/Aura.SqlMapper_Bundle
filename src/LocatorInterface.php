<?php
namespace Aura\SqlMapper_Bundle;

interface LocatorInterface extends \IteratorAggregate
{
    /**
     *
     * Constructor.
     *
     * @param array $members An array of key-value pairs where the key is a
     * name and the value is a callable that returns a mapper instance.
     *
     */
    public function __construct(array $members);

    /**
     *
     * IteratorAggregate: Returns an iterator for this locator.
     *
     * @return LocatorIteratorInterface
     *
     */
    public function getIterator();

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
    public function __get($key);
}