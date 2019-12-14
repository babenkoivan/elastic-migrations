<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration;

use ElasticClient\ServiceProvider as ClientServiceProvider;
use ElasticMigrations\ServiceProvider as MigrationsServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            MigrationsServiceProvider::class,
            ClientServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('elastic.migrations.directory', realpath(__DIR__ . '/../migrations'));
    }
}
