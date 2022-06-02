<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration;

use Elastic\Client\ServiceProvider as ClientServiceProvider;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Migrations\ServiceProvider as MigrationsServiceProvider;
use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Psr\Http\Client\ClientInterface;

class TestCase extends TestbenchTestCase
{
    protected Repository $config;

    protected function getPackageProviders($app): array
    {
        return [
            MigrationsServiceProvider::class,
            ClientServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        /** @var Repository $config */
        $config = $app['config'];
        $config->set('elastic.migrations.database.table', 'test_elastic_migrations');
        $config->set('elastic.migrations.storage', realpath(__DIR__ . '/../migrations'));
        $this->config = $config;

        $app->singleton(Client::class, function () {
            $httpClientMock = $this->createMock(ClientInterface::class);

            return ClientBuilder::create()
                ->setHttpClient($httpClientMock)
                ->build();
        });
    }
}
