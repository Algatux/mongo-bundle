# Facile.it MongoDB Bundle

This bundle integrates the official [mongodb/mongo-php-library] driver library ([mongodb/mongodb] on packagist) 
with your Symfony app.

[![PHP](https://img.shields.io/badge/php-%5E7.2%7C%5E8.0-blue.svg)](https://img.shields.io/badge/php-%5E7.0-blue.svg)
[![MongoDB](https://img.shields.io/badge/MongoDB-%5E3.0-lightgrey.svg)](https://img.shields.io/badge/MongoDB-%5E3.0-lightgrey.svg)
[![ext-mongodb](https://img.shields.io/badge/ext_mongodb-%5E1.1.5-orange.svg)](https://img.shields.io/badge/ext_mongodb-%5E1.1.5-orange.svg)

[![Latest Stable Version](https://poser.pugx.org/facile-it/mongodb-bundle/v/stable)](https://packagist.org/packages/facile-it/mongodb-bundle)
[![Latest Unstable Version](https://poser.pugx.org/facile-it/mongodb-bundle/v/unstable)](https://packagist.org/packages/facile-it/mongodb-bundle) [![Total Downloads](https://poser.pugx.org/facile-it/mongodb-bundle/downloads)](https://packagist.org/packages/facile-it/mongodb-bundle) 
[![License](https://poser.pugx.org/facile-it/mongodb-bundle/license)](https://packagist.org/packages/facile-it/mongodb-bundle)

[![Flex Support](https://img.shields.io/badge/Flex-supported-brightgreen.svg)]()
![CI](https://github.com/facile-it/mongodb-bundle/workflows/CI/badge.svg)
![Static analysis](https://github.com/facile-it/mongodb-bundle/workflows/Static%20analysis/badge.svg)
[![codecov](https://codecov.io/gh/facile-it/mongodb-bundle/branch/master/graph/badge.svg?token=gEhvCteV7k)](https://codecov.io/gh/facile-it/mongodb-bundle)

[mongodb/mongo-php-library]: https://github.com/mongodb/mongo-php-library
[mongodb/mongodb]: https://packagist.org/packages/mongodb/mongodb

## Features

- Provide instances of `MongoDB\Database` as [services](#services) for your Symfony app.
- Add a [query profiling](#query-profiling) section to the profiler toolbar.
- Capability for loading [data fixtures](#fixtures).

## Installation

Make your project require this bundle with composer.

```bash
composer require facile-it/mongodb-bundle
```

If your project uses Symfony Flex, then it will also use the [recipe] for enabling the bundle and installing a default
configuration.

[recipe]: https://github.com/symfony/recipes-contrib/tree/master/facile-it/mongodb-bundle

If you don't have Symfony Flex installed, then you need to manually register the bundle in the `AppKernel` class.

```php
// app/AppKernel.php
class AppKernel extends Kernel {

    public function registerBundles()
    {
        return [
            // ...
            new Facile\MongoDbBundle\FacileMongoDbBundle(),
        ];
    }
}
```

Eventually, you will need to [configure](#configuration) it.

## Configuration

Here is the configuration reference:

```yaml
mongo_db_bundle:

  data_collection: true # set to false to disable data collection

  # clients section, here you can define connection to different servers or with different credentials
  clients:

    foo_client_name: # choose your client name
      uri: 'mongodb://host1:3062,host2' # default null (will use hosts to build connection URI)
      hosts: # required if uri is not set - will compose your connection URI (mongodb://host1:3062,host2:27017)
        - { host: host1, port: 3062 } # this 
        - { host: host2 }
      username: 'what-a-secret'
      password: 'what-a-secret'
      authSource: '' # the database name with the user’s credentials
      replicaSet: '' # default null (no replica) (experimental)
      ssl: false
      connectTimeoutMS: 3000 # default null (no timeout)
      readPreference: primaryPreferred # see https://docs.mongodb.com/manual/reference/read-preference/#primary for info

    other_client: ~ # same as upper configuration

  # connections section, these represents your Database object reference
  connections:

    foo_db:
      client_name: foo_client_name # Required - your client name in clients section
      database_name: 'foo_db' # Required

    other_db:
      client_name: ~
      database_name: ~

    foo_db_2:
      client_name: ~
      database_name: ~

  # Service reference to provide driver options - see example below
  driverOptions: "App\Services\DriverOptionsProvider" # default null
```

### Driver options

You might need to specify some driver options for constructing the `MongoDB\Client`. Read the [reference] for a complete
explanation of all the available options.

Implement `DriverOptionsInterface` and declare the class as a Symfony service.

```php
namespace App\Services;

use Facile\MongoDbBundle\Services\DriverOptions\DriverOptionsInterface;

final class MyCustomDriverOptionsProvider implements DriverOptionsInterface
{
    /** @var string */
    private $cafile;
    
    public function __construct(string $cafile) {
        $this->cafile = $cafile;
    }

    public function buildDriverOptions(array $clientConfiguration) : array {
        return [
            'allow_invalid_hostname' => false,
            'context' => ['cafile' => $this->cafile] // This option is deprecated, but let me use that as an example.
        ];
    }
}
```

```yaml
# config/services.yaml
App\Services\MyCustomDriverOptionsProvider:
  arguments:
    $cafile: 'example/route/file.crt'
```

Then use its service id as value of `driverOptions` in the bundle configuration.

```yml
# config/packages/facile_it_mongodb.yaml
mongo_db_bundle:
  driverOptions: "App\Services\MyCustomDriverOptionsProvider"
  # ...
```

[reference]: https://www.php.net/manual/en/mongodb-driver-manager.construct.php

## Services

You can directly access to the `MongoDB\Database` with these services:

```php
$this->get('mongo.connection'); // Default connection (first declared)
$this->get('mongo.connection.{connectionName}'); // [test_db, other_db, test_db_2] for example
```

To manipulate the database, please read the [official documentation].

[official documentation]: http://mongodb.github.io/mongo-php-library/classes/database/

## Query Profiling

On dev environment all queries executed by the library `MongoDB\Collection` class are profiled and showed inside the
Symfony web profiler.

[![Profiler Toolbar](https://github.com/facile-it/mongodb-bundle/blob/master/docs/img/profiler_toolbar.png)](https://github.com/facile-it/mongodb-bundle/blob/master/docs/img/profiler_toolbar.png)

[![Profiler Panel](https://github.com/facile-it/mongodb-bundle/blob/master/docs/img/profiler_panel.png)](https://github.com/facile-it/mongodb-bundle/blob/master/docs/img/profiler_panel.png)

## Fixtures

This bundle supports doctrine style fixtures.

To create one, define a class implementing the `MongoFixtureInterface` interface. Probably you would like to access to
the service container: make sure the class extends
`AbstractContainerAwareFixture`.

```php
namespace Path\To\Fixtures;

use Facile\MongoDbBundle\Fixtures\AbstractContainerAwareFixture;
use Facile\MongoDbBundle\Fixtures\MongoFixtureInterface;
use MongoDB\Collection;

final class Fixture extends AbstractContainerAwareFixture implements MongoFixtureInterface
{
    public function loadData(): void {
         /** @var Collection $collection */
        $collection = $this->getContainer()
            ->get('mongo.connection.foo_db')
            ->selectCollection($this->collection());

        $collection->insertOne([
            'title' => 'Dr. Strangelove or: How I Learned to Stop Worrying and Love the Bomb',
            'directedBy' => 'Stanley Kubrick',
            'releaseDate' => new \MongoDB\BSON\UTCDateTime(new \DateTime('1964-01-29 00:00:00')),
        ]);
    }

    public function loadIndexes(): void {
        /** @var Collection $collection */
        $collection = $this->getContainer()
            ->get('mongo.connection.foo_db')
            ->selectCollection($this->collection());

        $collection->createIndex(['releaseDate' => 1]);
    }

    public function collection(): string {
        return 'movies';
    }
}
```

Then, load it with the following Symfony command.

    mongodb:fixtures:load [--connection <connection-name>] <path/to/the/fixtures>

### Other Commands

To drop the database:

    mongodb:database:drop [--connection <connection-name>]

To drop a collection:

    mongodb:collection:drop [--connection <connection-name>] <collection_name>

### Ordering

You might have a fixture that requires the data loaded by another one.
In this case, the loading order is crucial.

Since [0.6.6] it's possible to specify the order by which the fixtures are loaded.

Make sure they implement `OrderedFixtureInterface`. The lower the value returned by `getOrder` the higher will be the
loading priority.

```php
namespace Path\To\Fixtures;

use Facile\MongoDbBundle\Fixtures\AbstractContainerAwareFixture;
use Facile\MongoDbBundle\Fixtures\MongoFixtureInterface;
use Facile\MongoDbBundle\Fixtures\OrderedFixtureInterface;

final class FixtureA extends AbstractContainerAwareFixture implements MongoFixtureInterface, OrderedFixtureInterface
{
    public function getOrder() : int {
        return 0;
    }

    public function collection(): string {
        return 'collection_a';
    }
    
    public function loadData(): void { /* ... */ }
    public function loadIndexes(): void { /* ... */ }
}

final class FixtureB extends AbstractContainerAwareFixture implements MongoFixtureInterface, OrderedFixtureInterface
{
    public function getOrder() : int {
        return 1;
    }

    public function collection(): string {
        return 'collection_b';
    }

    public function loadData(): void { /* ... */ }
    public function loadIndexes(): void { /* ... */ }
}
```

[0.6.6]: https://github.com/facile-it/mongodb-bundle/releases/tag/0.6.6

## Upgrading notes

### Upgrading to 1.1.0

The release [1.1.0] introduced a BC change in the configuration semantics.

Before 1.1.0, if not explicitly provided, the value of `authSource` was set to the database name specified in the
connection section, or to `admin` if even the database name was empty.

With 1.1.0, if the `authSource` key is not set in the configuration, it won't be set in connection options either.

Take a look to [CHANGELOG.md](CHANGELOG.md) for further details.

[1.1.0]: https://github.com/facile-it/mongodb-bundle/releases/tag/1.1.0

## Contributing

Feel free to contribute by opening a pull request.
Bug fixes or feature suggestions are always welcome.

### Development environment

If you like docker then there are some scripts to set up an insulated development environment.
Please be sure to have `docker` and `docker-compose` installed on your system.

To set up the project:
```bash
make setup
```

To start the container and log into the php:
```bash
make start
```

To execute the test suite:
```bash
make test
```

Note: All the above are meant to be used outside the containers.

### Port binding setup

During the set up a `docker-compose.override.yml` file is created from the `docker-compose.override.yml.dist` template.
Change the port binding configuration inside it to fit you needs.
