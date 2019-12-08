<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration;

use ElasticMigrations\ServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class
        ];
    }
}
