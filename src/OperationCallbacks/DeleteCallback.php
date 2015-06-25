<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

class DeleteCallback implements TransactionCallbackInterface
{
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