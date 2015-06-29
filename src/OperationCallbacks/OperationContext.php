<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\RowCacheInterface;

/**
 * Value store object that describes a data base action, Select, Insert, Update, or Delete
 */
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
     *
     * @param RowCacheInterface $cache From the appropriate row data mapper
     *
     * @param \stdClass $row Object that represents the appropriate row data, can be obtained form Row Data Extractor
     *
     * @param string $mapper_name name of the row data mapper
     *
     * @param string $relation_name name of the relation that this row data belongs to according to aggregate map
     *
     * @return OperationContext
     *
     */
    public function __construct(\stdClass $row, $mapper_name, $relation_name, RowCacheInterface $cache = null)
    {
        $this->cache = $cache;
        $this->row = $row;
        $this->mapper_name = $mapper_name;
        $this->relation_name = $relation_name;
    }
}