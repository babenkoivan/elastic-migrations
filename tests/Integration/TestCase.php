<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Tests\Integration;

use GuzzleHttp\Ring\Client\CurlHandler;
use Illuminate\Config\Repository;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;
use OpenSearch\Laravel\Client\ServiceProvider as ClientServiceProvider;
use OpenSearch\Migrations\ServiceProvider as MigrationsServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

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

        $this->config = $app['config'];
        $this->config->set('opensearch.migrations.database.table', 'test_opensearch_migrations');
        $this->config->set('opensearch.migrations.storage.default_path', realpath(__DIR__ . '/../migrations'));

        $app->singleton(Client::class, function () {
            $httpClientMock = $this->createMock(CurlHandler::class);

            return ClientBuilder::create()
                ->setHandler($httpClientMock)
                ->build();
        });
    }
}
