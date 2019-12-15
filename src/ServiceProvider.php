<?php
declare(strict_types=1);

namespace ElasticMigrations;

use Illuminate\Support\ServiceProvider as AbstractServiceProvider;

final class ServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    private $configPath;

    /**
     * {@inheritDoc}
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->configPath = realpath(__DIR__ . '/../config/elastic.migrations.php');
    }

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->configPath,
            basename($this->configPath, '.php')
        );
    }

    public function boot()
    {
        $this->publishes([
            $this->configPath => config_path(basename($this->configPath))
        ]);

        $this->loadMigrationsFrom(realpath(__DIR__ . '/../database/migrations'));
    }
}
