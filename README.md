# Aura.SqlMapper_Bundle

> This is very much a work in progress. Use at your own risk.

## Foreword

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

### Entity and Factory

```php
<?php
use Aura\SqlMapper_Bundle\ObjectFactory;

class Post
{
    public $id;
    public $title;
    public $body;

    public function __construct(array $data = array())
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }
}

class PostFactory extends ObjectFactory
{
    public function newObject(array $row = array())
    {
        return new Post($row);
    }
}
?>
```

### Gateway
```php
<?php
use Aura\SqlMapper_Bundle\AbstractGateway;

class PostGateway extends AbstractGateway
{
    public function getTable()
    {
        return 'posts';
    }

    public function getPrimaryCol()
    {
        return 'id';
    }
}
?>
```

### Mapper

```php
<?php
use Aura\SqlMapper_Bundle\AbstractMapper;

class PostMapper extends AbstractMapper
{
    public function getIdentityField()
    {
        return 'id';
    }

    public function getColsFields()
    {
        return [
            'id'    => 'id',
            'title' => 'title',
            'body'  => 'body',
        ];
    }
}
?>
```

## Usage

```php
<?php
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdo;
use Aura\SqlMapper_Bundle\Query\ConnectedQueryFactory;
use Aura\SqlMapper_Bundle\Filter;
use Aura\SqlQuery\QueryFactory;
use Aura\Sql\Profiler;

$profiler = new Profiler();
$connection_locator = new ConnectionLocator(function () use ($profiler) {
    $pdo = new ExtendedPdo('sqlite::memory:');
    $pdo->setProfiler($profiler);
    return $pdo;
});

$query = new ConnectedQueryFactory(new QueryFactory('sqlite'));

$gateway_filter = new Filter();
$gateway = new PostGateway($connection_locator, $query, $gateway_filter);

$object_factory = new PostFactory();
$mapper_filter = new Filter();
$mapper = new PostMapper($gateway, $object_factory, $mapper_filter);
?>
```

## Insert

```php
<?php
$object = new Post(array(
    'id' => null,
    'title' => 'Hello aura',
    'body' => 'Some awesome content',
));

$mapper->insert($object);
?>
```

### fetchObject

```php
<?php
$post = $mapper->fetchObject(
    $mapper->select()->where('id = ?', 1)
);
?>
```

### fetchObjectBy

```php
<?php
$post = $mapper->fetchObjectBy('id', 1);
?>
```

### fetchCollection

```php
<?php
$posts = $mapper->fetchCollection(
    $mapper->select()->where('id < ?', 11)
);
?>
```

### fetchCollectionBy

```php
<?php
$posts = $mapper->fetchCollectionBy('id', [1, 2, 3]);
?>
```

## Update

```php
<?php
$post = $mapper->fetchObjectBy('id', 1)
$post->title = 'Changed the title';
$mapper->update($post);
?>
```

## Update only changes

```php
<?php
$initial = $mapper->fetchObjectBy('id', 1)

$post = clone $initial;
$post->body = 'Changed the body';

$mapper->update($post, $initial);
?>
```

## Delete

```php
<?php
$post = $mapper->fetchObjectBy('id', 1);
$mapper->delete($post);
?>
```

## Object and Collection Factory

By default the mapper returns standard class objects. You can change this
behaviour when creating the mapper, by extending _ObjectFactory_ or by
implmenting _ObjectFactoryInterface_.


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
        $coll = array();
        foreach ($rows as $row) {
            $coll[] = $this->newObject($row);
        }
        return $coll;
    }
}

$object_factory new PostFactory();
$mapper_filter = new Filter();
$mapper = new PostMapper($gateway, $object_factory, $mapper_filter);
?>
```


## Override identity field

By default, mapper assumes a public property as the identity field (or one that appears public via the magic __set() method). If the individual object uses a different property name, or uses a method instead, override `setIdentityValue` method to provide setter functionality.

Example :

```php
<?php
namespace Vendor\Package;

use Aura\SqlMapper_Bundle\AbstractMapper;

class PostMapper extends AbstractMapper
{
    public function setIdentityValue($object, $value)
    {
        $object->setId($value);
    }
    // more code
}
?>
```
