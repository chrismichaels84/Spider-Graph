# Getting Started
You want to get started with Spider? Shinny.

*Please be aware that Spider is still in development. As we march toward 1.0, things will inevetably change. We do our best to keep code stable and secure, but we are still architecting the awesome. Please [get involved!](contributing.md)

## What are Graph Databases?
[Graph databases](https://en.wikipedia.org/wiki/Graph_database) are NoSql databases (not-only-sql) that treat relationships like first-class citizens.
That means that relationships can have properties.

For instance: character:Zoe -> is_married_to -> character:Wash.
Now, we can add properties to the relationship (is_married_to): years = 3.

This may seem like a simple thing, but it gives an enormous amount of power. Now we can discover paths, follow relationships, do nested queries, and get friends of friends of friends. All blazing fast compared to SQL.

For a more thorough introduction to graph databases, check out:
  * http://phoenixlabstech.org/2014/08/19/php-graph-databases-and-the-future/
  * http://neo4j.com/developer/graph-database/
  * http://www.slideshare.net/maxdemarzi/introduction-to-graph-databases-12735789
  * http://markorodriguez.com/

## Requirements and Supported Datastores
Requires:
  * PHP 5.4
  
Currently supports:
  * OrientDB 2.*
  * Neo4j 2.*
  * Gremlin Server 3.*-incubating (querybuilder not yet supported)
  
It's easy to [add your own driver](create-driver.md)

## Install
Via Composer
``` bash
$ composer require spider/spider
```

The `master` branch contains stable code, though not necessarily ready for production.
The `develop` branch is a step ahead and may me unstable right now.

## Connections
A connection holds the driver to a datastore and whatever settings that driver needs to connect (username, host, port, etc). 

Creating a new connection is as easy as filling it with configuration properties:
```php
$connection = new Connection([
    'driver' = 'orientdb',
    'hostname' = 'hostname'
    // and the rest of your credentials
]);
```

Three drivers ship with Spider: `orientdb`, `neo4j`, and `gremlin` (gremlin server).
You may also specify any class that implements `Spider\Drivers\DriverInterface` if you are [creating your own driver](create-driver.md)

Connections also inherit from [michaels/data-manager](http://github.com/chrismichaels84/data-manager), so you have access to get(), set, has(), etc using dot notation for all properties.
```php
$connection->set('port', 2424);
```

Once you have a connection, you can dive straight into the database.
```php
$connection->open();
$query = new Command("SELECT FROM V where name = 'Jayne Cobb'");
$response = $connection->executeReadCommand($query); // only accepts Commands
$response = $response->getSet(); // turns a raw response into a Collection
$connection->close();

echo $response->name; // Jayne Cobb
echo $response->favorite; // Bertha
```
This, though requires you to write out every query and manage the connection and response formats yourself.
We recommend you use the [Query Builder](command-builder.md) for that.

### Managing Connections
For convenience, a connection manager is included.
You can store multiple connections, each with their own driver and configuration.

The credentials include *at least* a `default` driver name, and the configuration for that driver.

```php
$manager = new Spider\Connections\Manager([
    'default' => 'default-connection',
    'default-connection' => [
        'driver'   => 'neo4j',
        'whatever' => 'options',
        'the' => 'driver',
        'needs' => 'to connect'
    ],
    'connection-two' => [
        'driver' => 'Some\Other\Full\Namespace\Class',
        'whatever' => 'credentials',
        'are' => 'needed'
    ]
]);

$defaultConnection = $manager->make();
$connectionTwo = $manager->make('connection-two');
```
The connection manager also inherits from [michaels/data-manager](http://github.com/chrismichaels84/data-manager).

### Fetching and Caching Connections
Anytime you `make()` a connection it will be cached so you can draw the same connection again.
You can get that cached connection via `fetch()` which will also create a new connection if it has not already been `make()`d
```php
$manager->fetch(); // Will return cached default connection, or create then cache it before returning
$manager->fetch('connection-name'); // same
```