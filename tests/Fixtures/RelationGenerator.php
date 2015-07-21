<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;
use Aura\SqlMapper_Bundle\Entity\EntityMapperInterface;
use Aura\SqlMapper_Bundle\Relations\Relation;

class RelationGenerator
{
    public function getRelations(array $entities)
    {
        $relations = [];
        foreach ($entities as $entity => $mapper) {
            $method = 'get'.ucfirst($entity);
            $relations[strtolower($entity)] = $this->$method($mapper);
        }
        return $relations;
    }

    protected function getBuilding(EntityMapperInterface $mapper)
    {
        return [
            'mapper' => $mapper,
            'relations' => [
                new Relation('building_type', 'type', 'code', true, Relation::HAS_ONE)
            ]
        ];
    }

    protected function getBuildingType(EntityMapperInterface $mapper)
    {
        return [
            'mapper' => $mapper,
            'relations' => null
        ];
    }

    protected function getTask(EntityMapperInterface $mapper)
    {
        return [
            'mapper' => $mapper,
            'relations' => null
        ];
    }

    protected function getFloor(EntityMapperInterface $mapper)
    {
        return [
            'mapper' => $mapper,
            'relations' => null
        ];
    }

    protected function getUser(EntityMapperInterface $mapper)
    {
        return [
            'mapper' => $mapper,
            'relations' => [
                new Relation('task', 'id', 'userid', false, Relation::HAS_MANY)
            ]
        ];
    }
}