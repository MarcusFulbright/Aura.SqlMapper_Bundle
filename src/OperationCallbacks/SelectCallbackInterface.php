<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\Aggregate\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\EntityMediation\OperationArranger;
use Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver;


/**
 * Describes how select Callbacks function.
 *
 * To select entire row dat objects, see the SelectCallback
 * To select Identifier fields, primary keys and foreign keys, see SelectIdentifierCallback
 */
interface SelectCallbackInterface
{
    /**
     * @param AggregateMapperInterface $mapper
     *
     * @param EntityRepository $entity_repository
     *
     * e@param OperationArranger $arranger
     *
     * @param PlaceholderResolver $resolver
     *
     */
    public function __construct(
        AggregateMapperInterface $mapper,
        EntityRepository $entity_repository,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    );

    /**
     * @param array $path Array of OperationContext objects in the order to execute them.
     */
    public function __invoke(array $path);
}