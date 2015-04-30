<?php
namespace Aura\SqlMapper_Bundle;

use Aura\SqlMapper_Bundle\MapperInterface;

/**
 * Interface ContextInterface
 */
interface ContextInterface
{
    /**
     * @param object $target The object we are going to traverse.
     * @param MapperInterface $mapper
     * @param object $initial_data An initial state of the object.
     */
    public function __construct(
        $target,
        MapperInterface $mapper,
        $initial_data = null
    );

    /**
     * Gets the next entry to operate on.
     *
     * Returns a \stdClass with three properties: tableName, row, and initial_row.
     *
     * @return \stdClass|bool
     */
    public function getNext();

    /**
     * Gets the current entry.
     *
     * Returns a \stdClass with three properties: tableName, row, and initial_row.
     *
     * @return \stdClass|bool
     */
    public function getCurrent();
}
