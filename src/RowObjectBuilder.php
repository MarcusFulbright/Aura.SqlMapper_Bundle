<?php
namespace Aura\SqlMapper_Bundle;

class RowObjectBuilder implements BuilderInterface
{
    /**
     *
     * An Aggregate Mapper locator.
     *
     * @var RowMapperLocator
     *
     */
    protected $row_mapper_locator;

    /**
     *
     * Constructor
     *
     * @param RowMapperLocator $row_mapper_locator The locator for
     * Aggregate Mappers.
     *
     */
    public function __construct(RowMapperLocator $row_mapper_locator)
    {
        $this->row_mapper_locator = $row_mapper_locator;
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
        return $row_mapper->fetchCollection($row_mapper->selectBy(key($criteria), current($criteria)));
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
        return $row_mapper->fetchObject($row_mapper->selectBy(key($criteria), current($criteria)));
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
            $query = $row_mapper->selectBy(key($criteria), current($criteria));
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
     * @return RowMapperInterface || false
     *
     */
    public function getMapper($mapper_name)
    {
        try {
            return $this->row_mapper_locator->__get($mapper_name);
        } catch (Exception\NoSuchMapper $e) {
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
     * @return RowMapperLocator
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
        return new RowMapperLocator($factories);
    }
}