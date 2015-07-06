<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\RowObjectBuilder;


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
     * @param RowObjectBuilder $row_builder
     *
     * e@param OperationArranger $arranger
     *
     * @param PlaceholderResolver $resolver
     *
     */
    public function __construct(
        AggregateMapperInterface $mapper,
        RowObjectBuilder $row_builder,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    );

    /**
     * @param array $path Array of OperationContext objects in the order to execute them.
     */
    public function __invoke(array $path);
}