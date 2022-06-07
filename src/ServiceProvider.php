<?php declare(strict_types=1);

namespace Elastic\Migrations;

use Elastic\Migrations\Adapters\IndexManagerAdapter;
use Elastic\Migrations\Console\FreshCommand;
use Elastic\Migrations\Console\MakeCommand;
use Elastic\Migrations\Console\MigrateCommand;
use Elastic\Migrations\Console\RefreshCommand;
use Elastic\Migrations\Console\ResetCommand;
use Elastic\Migrations\Console\RollbackCommand;
use Elastic\Migrations\Console\StatusCommand;
use Elastic\Migrations\Filesystem\MigrationStorage;
use Illuminate\Support\ServiceProvider as AbstractServiceProvider;

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

        $this->configPath = dirname(__DIR__) . '/config/elastic.migrations.php';
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
