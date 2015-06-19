<?php
/**
 * Created by IntelliJ IDEA.
 * User: conlanc
 * Date: 6/19/2015
 * Time: 4:00 PM
 */

namespace Aura\SqlMapper_Bundle;


interface RowDataArrangerInterface {

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