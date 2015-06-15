<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\UnitOfWork;

interface OperationCallbackInterface
{
    public function __invoke(UnitOfWork $uow, OperationContext $context);
}