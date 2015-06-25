<?php
namespace Aura\SqlMapper_Bundle\OperationCallbacks;

use Aura\SqlMapper_Bundle\AggregateMapperInterface;
use Aura\SqlMapper_Bundle\MapperLocator;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;

interface SelectCallbackInterface
{
    public function __construct(
        AggregateMapperInterface $mapper,
        MapperLocator $locator,
        OperationArranger $arranger,
        PlaceholderResolver $resolver
    );

    public function __invoke(array $path);
}