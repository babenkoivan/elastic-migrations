# OpenSearch Migrations

OpenSearch Migrations for Laravel allow you to easily modify and share indices schema across the application's environments.

## Contents

* [Compatibility](#compatibility)
* [Installation](#installation) 
* [Configuration](#configuration)
* [Writing Migrations](#writing-migrations)
* [Running Migrations](#running-migrations)
* [Reverting Migrations](#reverting-migrations)
* [Starting Over](#starting-over)
* [Migration Status](#migration-status)
* [Zero Downtime Migration](#zero-downtime-migration)
* [Troubleshooting](#migration-status)

## Compatibility

The current version of OpenSearch Migrations has been tested with the following configuration:

* PHP 7.4-8.0
* OpenSearch 1.x
* Laravel 6.x-9.x

## Installation

The library can be installed via Composer:

```bash
composer require friendsofcat/opensearch-migrations
```

If you want to use OpenSearch Migrations with [Lumen framework](https://lumen.laravel.com/) check [this guide](https://github.com/babenkoivan/opensearch-migrations/wiki/Lumen-Installation).

## Configuration

OpenSearch Migrations uses [friendsofcat/opensearch-client](https://github.com/friendsofcat/opensearch-client) as a dependency.
To change the client settings you need to publish the configuration file first:

```bash
php artisan vendor:publish --provider="OpenSearch\Laravel\Client\ServiceProvider"
```

In the newly created `config/opensearch.client.php` file you can define the default connection name and describe multiple
connections using configuration hashes. Please, refer to the [opensearch-client documentation](https://github.com/friendsofcat/opensearch-client) for more details.

It is recommended to publish OpenSearch Migrations settings as well:

```bash
php artisan vendor:publish --provider="OpenSearch\Migrations\ServiceProvider"
```

This will create the `config/opensearch.migrations.php` file, which allows you to configure the following options:

* `storage.default_path` - the default location of your migration files
* `database.table` - the table name that holds executed migration names
* `database.connection` - the database connection you wish to use
* `prefixes.index` - the prefix of your indices
* `prefixes.alias` - the prefix of your aliases

If you store some migration files outside the default path and want them to be visible by the package, you may use 
`registerPaths` method to inform OpenSearch Migrations how to load them:

```php
class MyAppServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        resolve(MigrationStorage::class)->registerPaths([
            '/my_app/opensearch/migrations1',
            '/my_app/opensearch/migrations2',
        ]);
    }
}
```


Finally, don't forget to run Laravel database migrations to create OpenSearch Migrations table:

```bash
php artisan migrate
```

## Writing Migrations

You can effortlessly create a new migration file using an Artisan console command:

```bash
// create a migration file with "create_my_index.php" name in the default directory
php artisan opensearch:make:migration create_my_index

// create a migration file with "create_my_index.php" name in "/my_path" directory 
// note, that you need to specify the full path to the file in this case
php artisan opensearch:make:migration /my_path/create_my_index.php
```

Every migration has two methods: `up` and `down`. `up` is used to alternate the index schema and `down` is used to revert that action.

You can use `OpenSearch\Migrations\Facades\Index` facade to perform basic operations over OpenSearch indices:

#### Create Index

You can create an index with the default settings: 

```php
Index::create('my-index');
``` 

You can use a modifier to configure mapping and settings:

```php
Index::create('my-index', function (Mapping $mapping, Settings $settings) {
    // to add a new field to the mapping use method name as a field type (in Camel Case), 
    // first argument as a field name and optional second argument for additional field parameters  
    $mapping->text('title', ['boost' => 2]);
    $mapping->float('price');

    // you can define a dynamic template as follows
    $mapping->dynamicTemplate('my_template_name', [
        'match_mapping_type' => 'long',
        'mapping' => [
            'type' => 'integer',
        ],
    ]);
    
    // you can also change the index settings and the analysis configuration
    $settings->index([
         'number_of_replicas' => 2,
         'refresh_interval' => -1
    ]);
    
    $settings->analysis([
        'analyzer' => [
            'title' => [
                'type' => 'custom',
                'tokenizer' => 'whitespace'    
            ]
        ]
    ]);
});
```

There is also the `createRaw` method in your disposal:

```php
$mapping = [
    'properties' => [
        'title' => [
            'type' => 'text'
        ]
    ]
];

$settings = [
    'number_of_replicas' => 2
];

Index::createRaw('my-index', $mapping, $settings);
```

Finally, it is possible to create an index only if it doesn't exist:

```php
// you can use a modifier as shown above
Index::createIfNotExists('my-index', $modifier);
// or you can use raw mapping and settings 
Index::createIfNotExistsRaw('my-index', $mapping, $settings);
```

#### Update Mapping

You can use a modifier to adjust the mapping:

```php
Index::putMapping('my-index', function (Mapping $mapping) {
    $mapping->text('title', ['boost' => 2]);
    $mapping->float('price');
});
```

Alternatively, you can use the `putMappingRaw` method as follows:

```php
Index::putMappingRaw('my-index', [
    'properties' => [
        'title' => [
            'type' => 'text',
            'boost' => 2
        ],
        'price' => [
            'price' => 'float'
        ]      
    ]   
]);
```

#### Update Settings

You can use a modifier to change an index configuration:

```php
Index::putSettings('my-index', function (Settings $settings) {
    $settings->index([
         'number_of_replicas' => 2,
         'refresh_interval' => -1
    ]);
});
``` 

The same result can be achieved with the `putSettingsRaw` method:

```php
Index::putSettingsRaw('my-index', [
    'index' => [
        'number_of_replicas' => 2,
        'refresh_interval' => -1
    ]
]); 
```

It is possible to update analysis settings only on closed indices. The `pushSettings` method closes the index, 
updates the configuration and opens the index again:

```php
Index::pushSettings('my-index', function (Settings $settings) {
    $settings->analysis([
        'analyzer' => [
            'title' => [
                'type' => 'custom',
                'tokenizer' => 'whitespace'
            ]
        ]
    ]);
});
```

The same can be done with the `pushSettingsRaw` method:

```php
Index::pushSettingsRaw('my-index', [
    'analysis' => [
        'analyzer' => [
            'title' => [
                'type' => 'custom',
                'tokenizer' => 'whitespace'
            ]
        ]
    ]
]); 
```

#### Drop Index

You can unconditionally delete the index:

```php
Index::drop('my-index');
```

or delete it only if it exists:

```php
Index::dropIfExists('my-index');
```

#### Create Alias

You can create an alias with optional filter query:

```php
Index::putAlias('my-index', 'my-alias', [
    'is_write_index' => true,
    'filter' => [
        'term' => [
            'user_id' => 1,
        ],
    ],
]);
```

#### Delete Alias

You can delete an alias by its name:

```php
Index::deleteAlias('my-index', 'my-alias');
```

#### Multiple Connections

You can configure multiple connections to OpenSearch in the [client's configuration file](https://github.com/friendsofcat/opensearch-client/tree/master#configuration),
and then use a different connection for every operation:

```php
Index::connection('my-connection')->drop('my-index');
```

#### More

Finally, you are free to inject `OpenSearch\Client` in the migration constructor and execute any supported by client actions.

## Running Migrations

You can either run all migrations:

```bash
php artisan opensearch:migrate
```

or run a specific one:

```bash
// execute a migration located in one of the registered paths
php artisan opensearch:migrate 2018_12_01_081000_create_my_index

// execute a migration located in "/my_path" directory
// note, that you need to specify the full path to the file in this case
php artisan opensearch:migrate /my_path/2018_12_01_081000_create_my_index.php
```

Use the `--force` option if you want to execute migrations on production environment:

```bash
php artisan opensearch:migrate --force
```

## Reverting Migrations

You can either revert the last executed migrations:

```bash
php artisan opensearch:migrate:rollback 
```

or rollback a specific one:

```bash
// rollback a migration located in one of the registered paths
php artisan opensearch:migrate:rollback 2018_12_01_081000_create_my_index

// rollback a migration located in "/my_path" directory
// note, that you need to specify the full path to the file in this case
php artisan opensearch:migrate:rollback /my_path/2018_12_01_081000_create_my_index
```

Use the `opensearch:migrate:reset` command if you want to revert all previously migrated files:

```bash
php artisan opensearch:migrate:reset 
```

## Starting Over

Sometimes you just want to start over, rollback all the changes and apply them again:

```bash
php artisan opensearch:migrate:refresh
```

Alternatively you can also drop all existing indices and rerun the migrations:

```bash
php artisan opensearch:migrate:fresh
```

## Migration Status

You can always check which files have been already migrated and what can be reverted by the `opensearch:migrate:rollback` command (the last batch):

```bash
php artisan opensearch:migrate:status
```

## Zero Downtime Migration

Changing an index mapping with zero downtime is not a trivial process and might vary from one project to another.
OpenSearch Migrations library doesn't include such feature out of the box, but you can implement it in your project by [following this guide](https://github.com/babenkoivan/elastic-migrations/wiki/Changing-Mapping-with-Zero-Downtime).

## Troubleshooting

If you see one of the messages below, follow the instructions:

* `Migration table is not yet created` - run the `php artisan migrate` command
* `Migration directory is not yet created` - create a migration file using the `opensearch:make:migration` command or 
create `migrations` directory manually
  
In case one of the commands doesn't work as expected, try to publish configuration:

```bash
php artisan vendor:publish --provider="OpenSearch\Migrations\ServiceProvider"
```
