<?php
namespace Aura\SqlMapper_Bundle\Entity;

use Aura\SqlMapper_Bundle\Exception\NoSuchMember;
use Aura\SqlMapper_Bundle\RepositoryInterface;

class EntityRepository implements RepositoryInterface
{
    /**
     *
     * An Aggregate Mapper locator.
     *
     * @var EntityMapperLocator
     *
     */
    protected $locator;

    /**
     *
     * Constructor
     *
     * @param EntityMapperLocator $row_mapper_locator The locator for
     * Aggregate Mappers.
     *
     */
    public function __construct(EntityMapperLocator $row_mapper_locator)
    {
        $this->locator = $row_mapper_locator;
    }

    /**
     *
     * Returns a collection of the specified aggregate, each member of
     * which matches the provided criteria.
     *
     * @param  string $mapper_name The key of the row_mapper.
     *
     * @param  array  $criteria An array of criteria, describing the objects
     * to be returned.
     *
     * @return mixed An instance of the aggregate collection, as defined by
     * the AggregateMapper
     *
     */
    public function fetchCollection($mapper_name, array $criteria = [])
    {
        $row_mapper = $this->getMapper($mapper_name);
        return $row_mapper->fetchCollection($row_mapper->selectBy($criteria));
    }

    /**
     *
     * Returns a single instance of the specified aggregate that matches
     * the provided criteria.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param array $criteria An array of criteria, describing the object
     * to be returned.
     *
     * @return mixed An instance of the row, as defined by the
     * RowMapper
     *
     */
    public function fetchObject($mapper_name, array $criteria = array())
    {
        $row_mapper = $this->getMapper($mapper_name);
        return $row_mapper->fetchObject($row_mapper->selectBy($criteria));
    }

    /**
     *
     * Executes a select for all of the mappers in the indicated
     * row_mapper.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param array $criteria An array of criteria, describing (from the
     * object's perspective) the data to return.
     *
     * @return array An arranged array of arranged DB output.
     *
     */
    public function select($mapper_name, array $criteria = [])
    {
        $row_mapper = $this->getMapper($mapper_name);
        if (empty($criteria)) {
            $query = $row_mapper->select();
        } else {
            $query = $row_mapper->selectBy($criteria);
        }
        return $row_mapper->getWriteConnection()->fetchAll($query->__toString(), $query->getBindValues());
    }

    /**
     *
     * Executes an update for the provided object.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param mixed $object The aggregate instance to update.
     *
     * @return bool Whether or not the update was successful.
     *
     */
    public function update($mapper_name, $object)
    {
        $row_mapper = $this->getMapper($mapper_name);
        return (bool) $row_mapper->update($object);
    }

    /**
     *
     * Executes an save for the provided object.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param mixed $object The aggregate instance to save.
     *
     * @return bool Whether or not the create was successful.
     *
     */
    public function create($mapper_name, $object)
    {
        $row_mapper = $this->getMapper($mapper_name);
        return (bool) $row_mapper->insert($object);
    }

    /**
     *
     * Executes a delete for the provided object.
     *
     * @param string $mapper_name The key of the row_mapper.
     *
     * @param mixed $object The aggregate instance to delete.
     *
     * @return bool Whether or not the delete was successful.
     *
     */
    public function delete($mapper_name, $object)
    {
        $row_mapper = $this->getMapper($mapper_name);
        return (bool) $row_mapper->delete($object);
    }

    /**
     *
     * Resolves an aggregate mapper name to its mapper.
     *
     * @param string $mapper_name The name of the map to retrieve.
     *
     * @return EntityMapperInterface || false
     *
     */
    public function getMapper($mapper_name)
    {
        try {
            return $this->locator->__get($mapper_name);
        } catch (NoSuchMember $e) {
            return false;
        }
    }

    /**
     *
     * Creates a mapper locator populated with the mappers that correspond to the given mapper names.
     *
     * @todo throw exception if it can't find a mapperName
     *
     * @param array $mappers an array of mapper names
     *
     * @return EntityMapperLocator
     *
     */
    public function getLocatorForMappers(array $mappers)
    {
        $factories = [];
        foreach ($mappers as $mapper_name) {
            $row_mapper = $this->getMapper($mapper_name);
            $factories[$mapper_name] = function() use ($row_mapper) {
                return $row_mapper;
            };
        }
        return new EntityMapperLocator($factories);
    }
}