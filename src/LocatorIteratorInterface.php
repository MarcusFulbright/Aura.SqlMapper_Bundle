<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Interface LocatorIteratorInterface
 * @package Aura\SqlMapper_Bundle
 */
interface LocatorIteratorInterface
{
    /**
     *
     * Constructor.
     *
     * @param LocatorInterface $locator
     *
     * @param array $keys The keys in the RowMapperLocator object.
     *
     * @internal param LocatorInterface $mapper_locator The RowMapperLocator object over which to iterate.
     *
     */
    public function __construct(LocatorInterface $locator, array $keys = []);
}