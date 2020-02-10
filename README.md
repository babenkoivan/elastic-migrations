# Elastic Migrations

[![Build Status](https://travis-ci.com/babenkoivan/elastic-migrations.svg?token=tL2AyZUSS9biRsKPg7fp&branch=master)](https://travis-ci.com/babenkoivan/elastic-migrations)
[![WIP](https://img.shields.io/static/v1?label=WIP&message=work%20in%20progress&color=red)](#)

---

Elasticsearch migrations for Laravel allows you easily modify and share indices schema across the application's environments.

## Contents

* [Installation](#installation) 
* [Configuration](#configuration)
* [Writing Migrations](#writing-migrations)
* [Running Migrations](#running-migrations)
* [Reverting Migrations](#reverting-migrations)
* [Starting Over](#starting-over)
* [Migration Status](#migration-status)
* [Troubleshooting](#migration-status)

## Installation

The library can be installed via Composer:

```bash
composer require babenkoivan/elastic-migrations
```

## Configuration

Elastic Migrations uses [babenkoivan/elastic-client](https://github.com/babenkoivan/elastic-client) as a dependency. 
If you want to change the default client settings (and I'm pretty sure you do), then you need to create the configuration file first:

```bash
php artisan vendor:publish --provider="ElasticClient\ServiceProvider"
```

You can change Elasticsearch host and other client settings in the `config/elastic.client.php` file. Please refer to 
[babenkoivan/elastic-client](https://github.com/babenkoivan/elastic-client) for more details.

If you want to change migrations default table name or directory, publish Elastic Migrations settings as well:

```bash
php artisan vendor:publish --provider="ElasticMigrations\ServiceProvider"
```

You can find the mentioned above options in the `config/elastic.migrations.php` file.

Finally, don't forget to run Laravel database migrations to create Elastic Migrations table:

```bash
php artisan migrate
```

## Writing Migrations

You can effortlessly create a new migration file using Artisan console:

```bash
php artisan elastic:make:migration create_my_index
```

This command will create a migration file in the `elastic/migrations` directory (default value). Every migration file has a
name prefixed with a date and contains a migration class with two methods: `up` and `down`.

`up` method is used to alternate the index schema and `down` is used to revert that action.

You can use `ElasticMigrations\Facades\Index` facade to perform basic operations over Elasticsearch indices:

#### Create Index

Create an index with default settings: 

```php
Index::create('my-index');
``` 

or use a modifier to set up a mapping and settings:

```php
Index::create('my-index', function (Mapping $mapping, Settings $settings) {
    // the mapping fluent setters use the method name as the field type (it will be snake cased), 
    // the first parameter as the field name and the second (optional) parameter as the field additional settings
    $mapping->text('title', ['boost' => 2]);
    $mapping->float('price');
    
    // the settings fluent setters use the method name as the option name (it will be snake cased) and the parameter as the option value
    $settings->numberOfReplicas(2);
    $settings->refreshInterval(-1);
});
```

You can also create an index only if it doesn't exist:

```php
Index::createIfNotExists('my-index');
``` 

#### Update Mapping

Use the modifier to adjust the mapping:

```php
Index::putMapping('my-index', function (Mapping $mapping) {
    $mapping->text('title', ['boost' => 2]);
    $mapping->float('price');
});
```

#### Update Settings

Use the modifier to change the index configuration:

```php
Index::putSettings('my-index', function (Settings $settings) {
    $settings->numberOfReplicas(2);
    $settings->refreshInterval(-1);
});
``` 

You can update analysis settings only on closed indices. The `putSettingsHard` method closes the index, updates the configuration and
opens the index again:

```php
Index::putSettingsHard('my-index', function (Settings $settings) {
    $settings->analysis([
        'analyzer' => [
            'content' => [
                'type' => 'custom',
                'tokenizer' => 'whitespace'
            ]
        ]
    ]);
});
``` 

#### Drop Index

You can unconditionally delete the index:

```php
Index::drop('my-index');
```

or make sure, that the index exists before deleting it:

```php
Index::dropIfExists('my-index');
```

#### More

Finally, you are free to inject `Elasticsearch\Client` in the migration constructor and execute any actions, that client supports.

## Running Migrations

You can either run all migrations:

```bash
php artisan elastic:migrate
```

or run a specific file:

```bash
php artisan elastic:migrate 2018_12_01_081000_create_my_index
```

Use the `--force` option if you want to execute migrations on production environment:

```bash
php artisan elastic:migrate --force
```

## Reverting Migrations

You can either revert the last migrated files:

```bash
php artisan elastic:migrate:rollback 
```

or rollback a specific migration:

```bash
php artisan elastic:migrate:rollback 2018_12_01_081000_create_my_index
```

Use the `elastic:migrate:reset` command if you want to revert all previously migrated files:

```bash
php artisan elastic:migrate:reset 
```

## Starting Over

Sometimes you just want to start over and rollback all the changes to migrate them again immediately:

```bash
php artisan elastic:migrate:refresh
```

## Migration Status

You can always check which files has been already migrated and what can be reverted by the `elastic:migrate:rollback` command (the last batch):

```bash
php artisan elastic:migrate:status
```

## Troubleshooting

If you see one of the messages below, please execute the mentioned action:

* `Migration table is not yet created` - run the `php artisan migrate` command
* `Migration directory is not yet created` - create a migration file using the `elastic:make:migration` command or 
create a configured migrations directory manually   
