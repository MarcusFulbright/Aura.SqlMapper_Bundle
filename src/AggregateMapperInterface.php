<?php
namespace Aura\SqlMapper_Bundle;


use Aura\SqlQuery\Common\Select;

interface AggregateMapperInterface
{
    /**
     *
     * Returns the map of property addresses to mapper.field names.
     *
     * @return array
     *
     */
    public function getPropertyMap();
    public function getRelationMap();
    public function getRelationToMapper();

    public function newCollection($data);
    public function newObject($data);

    public function getRowCache();

}