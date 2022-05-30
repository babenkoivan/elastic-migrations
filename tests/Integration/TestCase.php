<?php declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration;

use Elastic\Client\ServiceProvider as ClientServiceProvider;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use ElasticMigrations\ServiceProvider as MigrationsServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Psr\Http\Client\ClientInterface;

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

        $app['config']->set('elastic.migrations.table', 'test_elastic_migrations');
        $app['config']->set('elastic.migrations.storage_directory', realpath(__DIR__ . '/../migrations'));

        $app->singleton(Client::class, function () {
            $httpClientMock = $this->createMock(ClientInterface::class);

            return ClientBuilder::create()
                ->setHttpClient($httpClientMock)
                ->build();
        });
    }
}
