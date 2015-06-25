<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

class UpdateCallback implements TransactionCallbackInterface
{
    public function __invoke(OperationContext $context)
    {
        $cache = $context->cache;
        $row = $context->row;
        $is_cached = $cache != null && $cache->isCached($row);
        $is_root = $context->relation_name === '__root';
        if ($is_cached || $is_root){
            $context->method = 'update';
        } else {
            $context->method = 'insert';
        }
        return $context;
    }
}