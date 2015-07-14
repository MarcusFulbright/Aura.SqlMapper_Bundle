<?php
namespace Aura\SqlMapper_Bundle;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlMapper_Bundle\Entity\EntityMapperLocator;
use Aura\SqlMapper_Bundle\EntityMediation\Transaction;
use Aura\SqlMapper_Bundle\Tests\Fixtures\FakeEntityFactory;
use Aura\SqlMapper_Bundle\Tests\Fixtures\FakeEntityMapper;
use Aura\SqlMapper_Bundle\Tests\Fixtures\FakeGateway;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Exception;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    protected $mapper_locator;

    protected $transaction;

    protected function setUp()
    {
        $connection_locator = new ConnectionLocator(function () {
            return new ExtendedPdo('sqlite::memory:');
        });

        $gateway = new FakeGateway(
            $connection_locator,
            new ConnectedQueryFactory(new QueryFactory('sqlite')),
            new Filter()
        );

        $mapper = new FakeEntityMapper(
            $gateway,
            new FakeEntityFactory(),
            new Filter()
        );

        $this->mapper_locator = new EntityMapperLocator([
            'fake' => function () use ($mapper) { return $mapper; },
        ]);

        $this->transaction = new Transaction($this->mapper_locator);
    }

    public function test__invoke()
    {
        $actual = $this->transaction->__invoke(
            [$this, 'success'],
            $this->mapper_locator
        );
        $this->assertSame('success', $actual);

        $this->setExpectedException('Exception', 'failure');
        $this->transaction->__invoke(
            [$this, 'failure'],
            $this->mapper_locator
        );
    }

    public function success(EntityMapperLocator $entity_mapper_locator)
    {
        return 'success';
    }

    public function failure(EntityMapperLocator $entity_mapper_locator)
    {
        throw new Exception('failure');
    }
}
