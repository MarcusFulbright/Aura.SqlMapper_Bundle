<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\OperationCallbacks\SelectIdentifierCallback;
use Mockery\MockInterface;

class SelectIdentifierCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var SelectIdentifierCallback */
    protected $callback;

    /** @var MockInterface */
    protected $builder;

    /** @var MockInterface */
    protected $resolver;

    public function setUp()
    {
        $this->builder = \Mockery::mock('Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface');
        $this->resolver = \Mockery::Mock('Aura\SqlMapper_Bundle\EntityMediation\PlaceholderResolver');
        $this->callback = new SelectIdentifierCallback(
            $this->builder,
            $this->resolver
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testInvoke()
    {
        $path = [

        ];
    }

}
