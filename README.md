

# Aura.SqlMapper_Bundle

> This is very much a work in progress. Use at your own risk.

## Foreword

### Definitions

* __Row Data__: An object and / or class that represents a single row of data from a single table
* __Aggregate Objects__: A cluster of associated row data objects that are treated as a unit for the purpose of data changes. One member is designated the root which keeps a set of consistency rules.
* __Field__: Attributes that sit on *Row Data Objects*
* __Property__: Attributes that sit on *Aggregate Objects*
* __Data Mapper__: Moves data between objects and a database while keeping them independent of each other and the mapper itself.
* __Row Mapper__: A *Data Mapper* that maps data base columns to *Fields* on *Row Data Objects*
* __Aggregate Mapper__: A *Data Mapper* that maps row data *Fields* to *properties* on aggregate objects.
* __Gateway__: Home of persistence logic for a single table.

### Goals

1. Abstract and separate the database implementation, persistence logic, and application representations of data.
2. Easily create several Domain Entities from the same data set.
3. Perform CRUD actions on objects in a uniform fashion.
4. Avoid maintaining large amounts of hand written SQL statements.

### Installation

This bundle is installable and autoloadable via Composer as [aura/sqlmapper-bundle](https://packagist.org/packages/aura/sqlmapper-bundle).

### Quality

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/auraphp/Aura.SqlMapper_Bundle/badges/quality-score.png?b=develop-2)](https://scrutinizer-ci.com/g/auraphp/Aura.SqlMapper_Bundle/?branch=develop-2)
[![Code Coverage](https://scrutinizer-ci.com/g/auraphp/Aura.SqlMapper_Bundle/badges/coverage.png?b=develop-2)](https://scrutinizer-ci.com/g/auraphp/Aura.SqlMapper_Bundle/?branch=develop-2)
[![Build Status](https://travis-ci.org/auraphp/Aura.SqlMapper_Bundle.svg?branch=develop-2)](https://travis-ci.org/auraphp/Aura.SqlMapper_Bundle)

To run the unit tests at the command line, issue `composer install` and then `phpunit` at the package root. This requires [Composer](http://getcomposer.org/) to be available as `composer`, and [PHPUnit](http://phpunit.de/manual/) to be available as `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

### Community

To ask questions, provide feedback, or otherwise communicate with the Aura community, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.

## Getting Started

This package allows developers to describe simple objects that just use data from one table, or aggregate objects that are composed of data from several different tables. This getting started guide take a 'bottom-up' approach and start with the *Row Data* objects and then describe how to combine them to make *Aggregate Objects*. For both cases, there are classes you will need to implement and code to add to your bootstrap.

### Row Data Manufacturing Layer
This layer focuses on creating *Row Data* objects. It consists of __Row Data Mappers__, __Gateways__, and a __Row Data Factory__.

#### Implementation

##### Gateway


*Gateways* serve as a thin wrapper around a single table in the database. All of the logic required to construct and execute a SQL statement lives here. 

You just need to extends `AbstractGateway` and implement two methods:

```php
<?php
use Aura\SqlMapper_Bundle\AbstractGateway;

class PostGateway extends AbstractGateway
{
    // Returns the table declaration the gateway is responsible for
    public function getTable()
    {
        return 'posts';
    }

    // returns the column definition for the primary key
    public function getPrimaryCol()
    {
        return 'id';
    }
}
?>
```


##### Row_Mapper

*Row_Mappers* have the responsibility of describing a map between database column definitions and attributes that exist on row_data objects. Object attributes and column definitions can have the same name, but they do not have to. This object exists to allow for more flexibility when naming or changing object attributes and database columns. 

Extend `AbstractRowMapper` and build out your map as follows:

```php
<?php
use Aura\SqlMapper_Bundle\AbstractRowMapper;

class PostMapper extends AbstractRowMapper
{
    // returns key value pairs of columns to attributes
    public function getColsFields()
    {
        return [
        //  column      attribute
            'id'    => 'id',
            'title' => 'title',
            'body'  => 'body',
            'category' => 'category'
        ];
    }
}
?>
```


##### Row Data Factory

This is a simple factory that returns row data objects or collections of row data objects. The default `ObjectFacotry` simply returns stdClass objects and arrays of stdClass objects for collections. You can change this behavior by extending _objectFactory_ or by implementing _ObjectFactoryInterface_.

A custom object factory will look like this:

```php
<?php
use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Filter;

class PostFactory implements ObjectFactoryInterface
{
    public function newObject(array $row = array())
    {
        return new Post($row);
    }

    public function newCollection(array $rows = array())
    {
        $coll = new PostCollection();
        foreach ($rows as $row) {
            $coll->addPost($this->newObject($row));
        }
        return $coll;
    }
}
?>
```



##### Row Data Object

The object representation of a single row from a single table. You can use `stdCalss` objects if you want, just have the *Row Data Factory* spit them out.

A custom implementation might look like this:

```php
<?php
use Aura\SqlMapper_Bundle\ObjectFactory;

class Post
{
    public $id;
    public $title;
    public $body;
    public $category

    public function __construct(array $data = array())
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }
}
?>
```

> `Note:` unless you are working with simple one table objects, this is not the same thing as the model or entity your application will use. That is the *Aggregate Object*


#### bootstrap

The boot strap code for the row data manufacturing layer looks like this:


```php
<?php
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlQuery\QueryFactory;
use Aura\Sql\Profiler;

//optional for debugging
$profiler = new Profiler();
//used to manage multiple DB connections (read/write)
$connection_locator = new ConnectionLocator(function () use ($profiler) {
    $pdo = new ExtendedPdo('sqlite::memory:');
    $pdo->setProfiler($profiler);
    return $pdo;
});
//used internally to generate sql queries
$query = new ConnectedQueryFactory(new QueryFactory('sqlite'));
//used to sanitize and validate at the gateway level
$gateway_filter = new Filter();
$gateway = new PostGateway($connection_locator, $query, $gateway_filter);

$object_factory = new PostFactory();
$mapper_filter = new Filter();
$mapper = new PostMapper($gateway, $object_factory, $mapper_filter);
?>
```


### The Aggregate Layer

Allows for combining multiple row data objects into a single *Aggregate Object*. This layer utilizes everything above and adds the following: __Row_Mapper_Locator__, __Aggregate_Mapper__, __Aggregate_Factory__, __Agregate_Mapper_Locator__, __Db_Mediator__, __Aggregate_Builder__. 

#### Implementation

##### Aggregate_Mapper

*Aggregate Mappers* describe how to map *fields* from *row data objects* to *properties* on the aggregate itself. Aggregate mappers use a *RowMapperLocator* to communicate with all the required row mappers and gateways. 


```php
<?php

use Aura\SqlMapper_Bundle\AbstractAggregateMapper;

class CategoryMapper extends AbstractAggregatemapper    
{
    // describes all properties on the aggregate and the mapper.field where they live on the row data object
    public function getPropertyMap()
    {
    //property               mapper_name.field
        'id'            => 'category_mapper.id',
        'name'          => 'category_mapper.name',
        'post.id'       => 'post_mapper.id',
        'post.title'    => 'post_mapper.title',
        'post.body'     => 'post_mapper.body',
        'post.category' => 'post_mapper.category'
    }

// describes all of the relationships on the aggregate object
    public function getRelationMap()
    {
        [
        //name of the property on the aggregate
            'post' => [
                //the property on the post row data object that represents it's side of the relationship.
                'join_property' => 'id',
                // where this property exists one level up, in this case on the category.
                'reference_field' => 'category_mapper.post',
                //can the relationship get broken by updating the join_property
                'owner' => false,
                // can be a hasOne to represent a one to one relationship to the parent or a hasMany to represent a one to many relationship to the parent.
                'type' => 'hasOne'
            ]
        ]
    }
}
?>
```


##### Aggregate Factory

This is a simple factory that returns aggregate objects or collections of aggregate objects. The default `AggregateObjectFacotry` simply returns stdClass objects and arrays of stdClass objects for collections. You can change this behavior by extending _objectFactory_ or by implementing _ObjectFactoryInterface_.

This factory differs from the *Row Data Factory* because the Aggregate factory understands how to handle relationships.


```php
<?php
use Aura\SqlMapper_Bundle\ObjectFactoryInterface;
use Aura\SqlMapper_Bundle\Filter;

class CategoryFactory implements ObjectFactoryInterface
{
    /** @var Post_Row_Factory */
    protected $post_factory;

    public function newCateogry(array $data = array())
    {
        $object = (object) $data;
        foreach ($data as $property => $value) {
            if ($property === 'post') {
                $object->posts[] = $this->post_factory->newObject($value);
            } else {
                $object->$property = $value;
            }
        }
        return $object;
    }

    public function newCollection(array $rows = array())
    {
        $coll = new CategoryCollection();
        foreach ($rows as $row) {
            $coll[] = $this->newObject($row);
        }
        return $coll;
    }
}
?>
```

> `Note:` When implementing your own factory, the input will be an array of row data objects grouped together based on their foreign key relationships. Your job is to parse through these row data objects to create aggregates.


#### Bootstrap

##### Row_Mapper_Locator

A [ServiceLocator](https://en.wikipedia.org/wiki/Service_locator_pattern) implementation for loading and retaining multiple mapper objects. No need to implement this class yourself, just include the following in a bootstrap:


```php
<?php
use Aura\SqlMapper_Bundle\RowMapperLocator;

$factories = [
    'post_mapper' => $post_row_mapper,
    'category_mapper' => $category_row_mapper
];

$row_locator = new RowMapperLocator($factories);

?>
```

##### Aggregate Mapper Locator

A [ServiceLocator](https://en.wikipedia.org/wiki/Service_locator_pattern) implementation for loading and retaining multiple aggregate mapper objects. No need to implement this class yourself, just include the following in a bootstrap:

> Putting these objects in a [DI](https://github.com/auraphp/Aura.Di) container is highly encouraged. 

```php
<?php
use Aura\SqlMapper_Bundle\AggregateMapperLocator;

$aggreagte_mappers = ['category_mapper' => $category_aggregate_mapper];

$aggregate_locator = new AggregateMapperLocator($aggregate_mappers);
?>
```

##### Db Mediator

The Db Mediator handles the extra complexity that aggregate objects introduce on database operations. The DbMediator relies on a few helper classes to help it determine the order of operations, and to break down aggregate objects into row_data objects. There is nothing to implement here, just add something like this to your bootstrap:


```php
<?php
use Aura\SqlMapper_Bundle\DbMediator;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\RowDataExtractor;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;

//used to determine order of transactions based on relationships
$arranger = new OperationArranger();

//a placeholder syntax is used for things like auto incrementing keys, this class helps the mediator resolve those placeholders to real values.
$resolver = new PlaceholderResolver();

//used to break aggregate objects down into row data objects.
$extractor = new RowDataExtractor();

//returns callbacks that compartmentalize specific decisions around persist operations.
$callback_factory = new CallbackFactory();

$mediator = new DbMediator(
    $row_data_locator,
    $arranger,
    $resolver,
    $extractor,
    $callback_factory);
?>
```


#### Aggregate Builder

The Aggregate Builder represents the point of contact for your application. The API defined here will have everything your application needs to concern it self with. There is nothing for you to implement, just more boilerplate for the bootstrap:


```php
<?php
use Aura\SqlMapper_Bundle\AggregateBuilder;

$aggregate_builder = new AggregateBuilder($aggregate_mapper_locator, $db_mediator);
?>
```


### Complete Bootstrap Example:

```php
<?php
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlQuery\QueryFactory;
use Aura\Sql\Profiler;
use Aura\SqlMapper_Bundle\RowMapperLocator;
use Aura\SqlMapper_Bundle\DbMediator;
use Aura\SqlMapper_Bundle\OperationArranger;
use Aura\SqlMapper_Bundle\PlaceholderResolver;
use Aura\SqlMapper_Bundle\RowDataExtractor;
use Aura\SqlMapper_Bundle\OperationCallbacks\OperationCallbackFactory;
use Aura\SqlMapper_Bundle\AggregateBuilder;

//optional for debugging
$profiler = new Profiler();
//used to manage multiple DB connections (read/write)
$connection_locator = new ConnectionLocator(function () use ($profiler) {
    $pdo = new ExtendedPdo('sqlite::memory:');
    $pdo->setProfiler($profiler);
    return $pdo;
});
//used internally to generate sql queries
$query = new ConnectedQueryFactory(new QueryFactory('sqlite'));
//used to sanitize and validate at the gateway level
$post_gateway_filter = new Filter();
$post_gateway = new PostGateway($connection_locator, $query, $post_gateway_filter);

$post_factory = new PostFactory();
$post_mapper_filter = new Filter();
$post_mapper = new PostRowMapper($post_gateway, $post_factory, $post_mapper_filter);

$category_gateway_filter = new Filter();
$category_gateway = new PostGateway($connection_locator, $query, $category_gateway_filter);

$category_factory = new CategoryFactory();
$category_mapper_filter = new Filter();
$category_mapper = new CategoryRowMapper($category_gateway, $category_factory, $category_mapper_filter);

$aggregate_factory = new CategoryFacotry();
$aggregate_mapper = new CategoryAggregateMapper($aggregate_factory);

$factories = [
    'post_mapper' => $post_mapper,
    'category_row_mapper' => $category_mapper
];

$row_locator = new RowMapperLocator($factories);

$aggreagte_mappers = ['category_aggregate_mapper' => $aggreagte_mapper];

$aggregate_locator = new AggregateMapperLocator($aggregate_mappers);

$mediator = new DbMediator(
    $row_locator,
    new OperationArranger(),
    new PlaceholderResolver(),
    new RowDataExtractor(),
    new OperationCallbackFactory();

$aggregate_builder = new AggregateBuilder($aggregate_mapper_locator, $db_mediator);
?>
```

### Working with Aggregates

With all the bootstrap code in place, you can perform CRUD actions on aggregates in the following manner:

#### Create

The create action is performed from the perspective of the root table. That row must be an insert action. All subsequent leaf tables can be *either* inserts *or* updates. The appropriate action will get determined by first checking the row mapper's cache then, if necessary, performing an extra select query.

```php
<?php
$category = new Category();
$category->setName('Docs');
$category->addPost($my_post);
$aggregate_builder->create('category_aggregate_mapper', $category);
?>
```

> When ever an create action gets performed, either by calling create or update, auto-incrementing keys are updated automatically by reference. Because of this, only a bool is returned to indicate success.


#### Read

Data can get fetched from the database in several forms: a collection of row objects, or as an aggregate object.

```php
<?php

$criteria = ['name' => 'Docs'];
//returns all row data objects grouped together by the aggregate root
$aggregate_builder->select('category_aggregate_mapper', $criteria);
//returns a single instantiated aggregate
$aggregate_builder->fetchObject('category_aggregate_mapper', $criteria);
//returns a collection of instantiated aggregates
$aggregate_builder->fetchCollection('category_aggregate_mapper', $criteria);
?>
```


#### Update

Updates work similarly to Crates. The root table must be an Update action. All subsequent leaf tables can be *either* inserts *or* updates. The appropriate action will get determined by first checking the row mapper's cache then, if necessary, performing an extra select query.

```php
<?php

$category->setName('NewName');
$aggregate_builder->update('category_aggregate_mapper', $category); 
?>
```

> When ever an update action gets performed, either by calling update or create, only the changes to your object are persisted and auto-incrementing keys are updated automatically by reference. Because of this, only a bool is returned to indicate success.

#### Delete

Deletes always delete __all__ records in the aggregate. To avoid deleting the posts associated with the category, we would need to remove the posts form the category before deleting the category. A bool is returned to indicate success.

```php
<?php
//make sure the posts don't get deleted
$category->posts = null;
$aggregate_builder->delete('category_aggregate_mapper', $category);
?>
```


### Row Data Cache

To improve performance, a caching mechanism can be used at the row data layer. The default `RowCache` employs a time to live and caches row data objects in memory using [spl object storage](http://php.net/manual/en/class.splobjectstorage.php). The `RowCacheInterface` allows you to build cache objects for any other caching mechanism you choose. These cache objects exist on the row mapper level and the row mappers query the cache before querying against the DB.

To use a Row Cache:

```php
<?php
//Primary key for the post table
$post_id = 'id';
//number of seconds to allow a row object to get cached
$post_ttl = 30;

$post_cache = new RowCache($post_id, $post_ttl);

$row_mapper = new PostRowMapper(
        $post_gateway,
        $post_factory,
        $post_mapper_filter,
        $post_cache
    );
?>
```


### Operation Callbacks

The *DbMedaitor* uses callbacks for each CRUD action to determine how to handle them appropriately. The `OperationCallbackFactory` spits out these callable objects for the DbMediator to use. If you want to augment behavior for a particular action, just extend the `OperationCallbackFactory` and inject your own callable object.

For Transactions, Create, Update, Delete, your callable must implement the `TransactionCallbackInterface`. For reads, Selects, use the `SelectCallbackInterface`.

### Known Constraints

This package comes with a set of known constraints:

* `AggregateBuilder::Select` & `AggregateBuilder::FetchObject` & `AggregateBuilderFetchCollection` can only take a single key value pair for the criteria argument. 
* Delete statements assume that everything on the aggregate getting passed in needs to get deleted from the DB. 
* Currently, individual queries for each row data object per aggregate are performed. In other words, no joins are used. This allows Aggregates to be composed of data from separate databases using different database connections. However, performance could still be optimized by grouping queries based on their connection.
* Row Data objects are essentially second class citizens behind Aggregate objects. You application can work with Row Data objects, you would just use the row data mappers in place of the Aggregate Builder. There is no way to type hint for both and could lead to duplicate code in your application.






