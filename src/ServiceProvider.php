<?php declare(strict_types=1);

namespace OpenSearch\Migrations;

use Illuminate\Support\ServiceProvider as AbstractServiceProvider;
use OpenSearch\Migrations\Adapters\IndexManagerAdapter;
use OpenSearch\Migrations\Console\FreshCommand;
use OpenSearch\Migrations\Console\MakeCommand;
use OpenSearch\Migrations\Console\MigrateCommand;
use OpenSearch\Migrations\Console\RefreshCommand;
use OpenSearch\Migrations\Console\ResetCommand;
use OpenSearch\Migrations\Console\RollbackCommand;
use OpenSearch\Migrations\Console\StatusCommand;
use OpenSearch\Migrations\Filesystem\MigrationStorage;

final class ServiceProvider extends AbstractServiceProvider
{
    private string $configPath;
    private string $migrationsPath;

    private array $commands = [
        MakeCommand::class,
        MigrateCommand::class,
        RefreshCommand::class,
        ResetCommand::class,
        RollbackCommand::class,
        StatusCommand::class,
        FreshCommand::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->configPath = dirname(__DIR__) . '/config/opensearch.migrations.php';
        $this->migrationsPath = dirname(__DIR__) . '/database/migrations';
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

        $this->app->singletonIf(MigrationStorage::class);
        $this->app->bindIf(IndexManagerInterface::class, IndexManagerAdapter::class);
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->configPath => config_path(basename($this->configPath)),
        ]);

        $this->loadMigrationsFrom($this->migrationsPath);

        $this->commands($this->commands);
    }
}
