<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\UnitOfWork;

class DeleteCallback implements OperationCallbackInterface
{
    public function __invoke(UnitOfWork $uow, OperationContext $context) {
        $cache = $context->cache;
        $row = $context->row;
        if ($cache === null || $cache != null && $cache->isCached($row) === true) {
            $uow->delete($context->mapper_name, $row);
        }
    }
}