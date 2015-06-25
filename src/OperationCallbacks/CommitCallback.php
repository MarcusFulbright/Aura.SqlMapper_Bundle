<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\Exception\DbOperationException;
use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\PlaceholderResolver;

/**
 *
 * Callback that can be passed to the Transaction class for execution.
 *
 * Parses the array of OperationContext items and performs the appropriate action on the correct mapper.
 *
 */
class CommitCallback
{
    /** @var array[OperationContext] */
    protected $operation_list;

    /** @var PlaceholderResolver */
    protected $resolver;

    /** @var MapperLocator */
    protected $locator;

    /** @var array */
    protected $extracted;

    /**
     *
     * @param array $operation_list Array of OperationContext objects in the correct order for execution
     *
     * @param PlaceholderResolver $resolver
     *
     * @param MapperLocator $locator
     *
     * @param array $extracted
     *
     */
    public function __construct(
        array &$operation_list,
        PlaceholderResolver $resolver,
        MapperLocator $locator,
        array $extracted
    ) {
        $this->operation_list = &$operation_list;
        $this->resolver       = $resolver;
        $this->locator        = $locator;
        $this->extracted      = $extracted;
    }

    /**
     *
     * @return bool Always returns true
     *
     * @throws DbOperationException If the operation cannot be executed.
     *
     */
    public function __invoke()
    {
        foreach ($this->operation_list as $context) {
            $mapper = $this->locator->__get($context->mapper_name);
            $method = $context->method;
            $this->resolver->resolveRowData($context->row, $this->extracted);
            try {
                $mapper->$method($context->row);
            } catch (\Exception $e) {
                throw new DbOperationException($e->getMessage());
            }
        }
        return true;
    }
}