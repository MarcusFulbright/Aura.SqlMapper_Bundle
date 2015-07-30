<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

use Aura\SqlMapper_Bundle\Aggregate\AggregateBuilderInterface;
use Aura\SqlMapper_Bundle\Entity\EntityRepository;
use Aura\SqlMapper_Bundle\Exception\DbOperationException;
use Aura\SqlMapper_Bundle\OperationCallbacks\CallbackFactoryInterface;
use Aura\SqlMapper_Bundle\OperationCallbacks\CommitCallback;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;

/**
 * Class that knows how to coordinate all of the desired DB operations.
 *
 * In charge of being the mediator between the aggregate domain layer and the DB layer. Any time a db operation is
 * required for an aggregate it should go through here. Db mediator knows how to craft Select Statements and to use
 * transactions for inserts, updates, and deletes.
 */
class EntityMediator implements EntityMediatorInterface
{
    /** @var EntityRepository */
    protected $entity_repository;

     /** @var  OperationArranger */
    protected $arranger;

    /** @var PlaceholderResolver */
    protected $placeholder_resolver;

    /** @var OperationCallbackFactory */
    protected $callback_factory;

    /**
     * @param EntityRepository $entity_repository
     * @param OperationArranger $operation_arranger
     * @param PlaceholderResolver $placeholder_resolver
     * @param EntityExtractor $extractor
     * @param CallbackFactoryInterface $callback_factory
     */
    public function __construct(
        EntityRepository $entity_repository,
        OperationArranger $operation_arranger,
        PlaceholderResolver $placeholder_resolver,
        CallbackFactoryInterface $callback_factory
    ) {
        $this->entity_repository    = $entity_repository;
        $this->operation_arranger   = $operation_arranger;
        $this->placeholder_resolver = $placeholder_resolver;
        $this->callback_factory     = $callback_factory;
    }

    /**
     *
     * Performs a select statement with optional criteria.
     *
     * When criteria is present, this method will select primary keys and join properties up to the root table, not the
     * actual row data objects. Root objects get selected based on the previously obtained primary key and leaf objects
     * get selected based on foreign key relationships.
     *
     * @todo Support multiple criteria
     *
     * @param AggregateBuilderInterface $builder
     *
     * @param OperationCriteria $criteria
     *
     * @throws Exception\NoSuchMapper
     *
     * @return array
     *
     */
    public function select(AggregateBuilderInterface $builder, OperationCriteria $criteria)
    {


    }

    /**
     *
     * Creates new data entries for the given object.
     *
     * For roots, inserts will always get performed, for leaves, inserts and updates will get performed appropriately.
     * Auto-incrementing primary keys will get updated on the given $obj by reference.
     *
     * @param AggregateBuilderInterface $builder The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $obj The instance of the object we want to create.
     *
     * @return object The given object.
     *
     * @throws Exception\DbOperationException When the transaction fails
     *
     */
    public function create(AggregateBuilderInterface $builder, $obj)
    {

    }

    /**
     *
     * Updates the provided object in the DB.
     *
     * For roots, update will always get performed, for leaves updates and inserts will get performed accordingly.
     * Auto-incrementing primary keys will get updated on the given obj by reference.
     *
     * @param AggregateBuilderInterface $builder The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $obj The instance of the object we want to update.
     *
     * @throws DbOperationException When the transaction fails
     *
     * @return object
     *
     */
    public function update(AggregateBuilderInterface $builder, $obj)
    {
        $pieces = $builder->deconstructAggregate($obj);
        $entity_order = $this->arranger->arrange($builder);
        $callback = $this->callback_factory->getUpdateCallback();
        $operation_list = $this->arranger->getOperations($entity_order, $pieces, $callback);
        $this->invokeTransaction(
            $this->callback_factory->getTransaction(),
            $this->callback_factory->getCommitCallback($this->placeholder_resolver)
        );
        return true;
    }

    /**
     *
     * Deletes the provided object from the DB.
     *
     * For now, everything on the root will get deleted from the DB. Destroy relationships by setting properties to null
     * before calling delete to avoid deleting them form the DB.
     *
     * @todo add a way to configure delete behavior.
     *
     * @param AggregateBuilderInterface $builder The mapper for the Aggregate Domain Object
     * we are concerned with.
     *
     * @param object $object The instance of the object we want to delete.
     *
     * @return object Whether or not this operation was successful.
     *
     * @throws DbOperationException When the Transaction fails
     *
     */
    public function delete(AggregateBuilderInterface $builder, $object)
    {

    }

    /**
     *
     * Invokes a transaction with the given callback in a try-catch.
     *
     * @param Transaction $transaction
     *
     * @param CommitCallback $call_back
     *
     * @param EntityMapperLocator $locator
     *
     * @throws DbOperationException When the transaction fails.
     *
     */
    protected function invokeTransaction(
        Transaction $transaction,
        CommitCallback $call_back,
        EntityMapperLocator $locator
    ) {
        try {
            $transaction->__invoke($call_back, $locator);
        } catch (\Exception $e) {
            throw new DbOperationException($e->getMessage());
        }
    }
}