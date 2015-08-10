<?php
namespace Aura\SqlMapper_Bundle\Tests\Unit;
use Aura\SqlMapper_Bundle\EntityMediation\OperationArranger;
use Mockery\MockInterface;

/**
 * Test QueryResolverTest
 * @package Aura\SqlMapper_Bundle
 */
class OperationArrangerTest extends \PHPUnit_Framework_TestCase
{
    /** @var OperationArranger */
    protected $arranger;

    /** @var MockInterface */
    protected $factory;

    /** @var MockInterface */
    protected $locator;

    /** @var MockInterface */
    protected $buildingTypeOperation;

    /** @var MockInterface */
    protected $buildingOperation;

    /** @var MockInterface */
    protected $userOperation;

    /** @var MockInterface */
    protected $builder;

    public function setUp()
    {
        $this->factory = \Mockery::mock('Aura\SqlMapper_Bundle\EntityMediation\EntityOperationFactory');
        $this->locator = \Mockery::mock('Aura\SqlMapper_Bundle\Relations\EntityLocatorInterface');
        $this->arranger = new OperationArranger(
            $this->factory,
            $this->locator
        );
        $this->buildingOperation = \Mockery::mock('Aura\SqlMapper_Bundle\EntityMediation\EntityOperation');
        $this->userOperation = \Mockery::mock('Aura\SqlMapper_Bundle\EntityMediation\EntityOperation');
        $this->builder = \Mockery::mock('Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface');

        $this->builder->shouldReceive('getRootRelation')->andReturn('user');
    }

    public function testGetPathToRoot()
    {

    }
}
