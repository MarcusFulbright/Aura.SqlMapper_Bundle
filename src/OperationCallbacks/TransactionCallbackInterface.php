<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

interface TransactionCallbackInterface
{
    public function __invoke(OperationContext $context);
}