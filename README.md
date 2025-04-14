# Stuff Versioned: MongoDB Backend

This is a backend implementation for the library [PHP Stuff Versioned](https://github.com/andreypostal/php-stuff-versioned).

## Installation

```
composer require andreypostal/php-stuff-versioned-mongo
```

## Usage

```php

$client = new MongoClient();
$db = $client->selectDB('my_db');

$backend = new \Andrey\StuffVersioned\Backend\Mongo\Mongo($db);

$manager = new \Andrey\StuffVersioned\VersionManager($backend);

// ...

```
