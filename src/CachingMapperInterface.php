<?php
/**
 * Created by IntelliJ IDEA.
 * User: conlanc
 * Date: 5/26/2015
 * Time: 1:27 PM
 */

namespace Aura\SqlMapper_Bundle;


interface CachingMapperInterface extends MapperInterface
{
    /**
     *
     * Returns the RowCache instance.
     *
     * @return RowCache
     *
     */
    public function getRowCache();
}