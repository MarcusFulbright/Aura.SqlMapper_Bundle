<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;

interface EntityArrangerInterface
{
    /**
     *
     * Transforms database output into the structure defined by the provided mapper.
     *
     * @param array $rows Database output, organized as row domain objects by relation name.
     *
     * @param AggregateMapperInterface $mapper The mapper that describes this dataset.
     *
     * @return array a multi-dimensional array
     *
     */
    public function arrangeRowData(array $rows, AggregateMapperInterface $mapper);
}