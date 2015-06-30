<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

class UpdateCallback implements TransactionCallbackInterface
{
    /**
     *
     * Logic used to decide what operation to perform on root update for a given context object.
     *
     * Roots will always be update, leafs can be update or delete accordingly.
     *
     * @param OperationContext $context
     *
     * @return OperationContext
     *
     */
    public function __invoke(OperationContext $context)
    {
        $mapper = $context->mapper;
        $row = $context->row;
        $exists = $mapper->rowExists($row);
        $is_root = $context->relation_name === '__root';
        if ($exists || $is_root){
            $context->method = 'update';
        } else {
            $context->method = 'insert';
        }
        return $context;
    }
}