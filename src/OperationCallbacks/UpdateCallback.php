<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\UnitOfWork;

class UpdateCallback implements OperationCallbackInterface
{
    public function __invoke(UnitOfWork $uow, OperationContext $context) {
        $cache = $context->cache;
        $row = $context->row;
        $is_cached = $cache != null && $cache->isCached($row);
        $is_root = $context->relation_name === '__root';
        if ($is_cached || $is_root){
            $uow->update($context->mapper_name, $row);
        } else {
            $uow->insert($context->mapper_name, $row);
        }
    }
}