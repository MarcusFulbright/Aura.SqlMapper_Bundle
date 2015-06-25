<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

/**
 * All transactional callback functions, Insert, Update, Delete should use this signature
 */
interface TransactionCallbackInterface
{
    /**
     * @param OperationContext $context
     */
    public function __invoke(OperationContext $context);
}