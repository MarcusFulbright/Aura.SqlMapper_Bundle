<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\Exception\DbOperationException;
use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\PlaceholderResolver;

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