<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;
use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\Entity\EntityMapperInterface;

/**
 * Value store object that describes a data base action, Select, Insert, Update, or Delete
 */
class OperationContext
{
    /** @var EntityMapperInterface  */
    public $mapper;

    /** @var \stdClass */
    public $row;

    /** @var string */
    public $relation_name;

    /** @var string */
    public $method;

    /**
     *
     * @param EntityMapperInterface $mapper The appropriate row data mapper
     *
     * @param \stdClass $row Object that represents the appropriate row data, can be obtained form Row Data Extractor
     *
     * @param string $relation_name name of the relation that this row data belongs to according to aggregate map
     *
     * @return OperationContext
     *
     */
    public function __construct(\stdClass $row, $relation_name, EntityMapperInterface $mapper)
    {
        $this->mapper = $mapper;
        $this->row = $row;
        $this->relation_name = $relation_name;
    }
}