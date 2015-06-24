<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

interface OperationCallbackInterface
{
    public function __invoke(OperationContext $context);
}