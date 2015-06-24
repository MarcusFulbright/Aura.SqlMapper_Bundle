<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\RowCacheInterface;

class OperationContext
{
    /** @var RowCacheInterface  */
    public $cache;

    /** @var \stdClass */
    public $row;

    /** @var string */
    public $mapper_name;

    /** @var string */
    public $relation_name;

    /** @var string */
    public $method;

    /**
     * @param RowCacheInterface $cache
     * @param \stdClass $row
     * @param $mapper_name
     * @param $relation_name
     */
    public function __construct(\stdClass $row, $mapper_name, $relation_name, RowCacheInterface $cache = null)
    {
        $this->cache = $cache;
        $this->row = $row;
        $this->mapper_name = $mapper_name;
        $this->relation_name = $relation_name;
    }
}