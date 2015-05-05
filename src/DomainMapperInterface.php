<?php
namespace Aura\SqlMapper_Bundle;

/**
 * Interface DomainMapperInterface
 * @package Aura\SqlMapper_Bundle
 */
interface DomainMapperInterface extends MapperInterface
{
    public function getPropertyMap();

    public function getMapperMap();

    public function getJoins();
}