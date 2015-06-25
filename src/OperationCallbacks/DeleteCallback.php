<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

class DeleteCallback implements TransactionCallbackInterface
{
    /**
     *
     * Logic used to decide what operation to perform on root delete for a given context object.
     *
     * Currently, everything gets deleted from the DB. Set properties to null on the aggregate object avoid deleting
     * undesired rows prior to calling delete.
     *
     * @todo add configuration for custom delete rules
     *
     * @param OperationContext $context
     *
     * @return OperationContext
     */
    public function __invoke(OperationContext $context)
    {
        $cache = $context->cache;
        $row = $context->row;
        if ($cache === null || $cache != null && $cache->isCached($row) === true) {
            $context->method = 'delete';
        }
        return $context;
    }
}