<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

class InsertCallback implements TransactionCallbackInterface
{
    /**
     *
     * Logic used to decide what operation to perform on root insert for a given context object
     *
     * Root object will always be an insert. Leafs will be insert or update accordingly.
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
        if ($exists && ! $is_root){
            $context->method = 'update';
        } else {
            $context->method = 'insert';
        }
        return $context;
    }
}