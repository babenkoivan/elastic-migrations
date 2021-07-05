<?php declare(strict_types=1);

namespace ElasticMigrations;

use ElasticMigrations\Adapters\IndexManagerAdapter;
use ElasticMigrations\Console\FreshCommand;
use ElasticMigrations\Console\MakeCommand;
use ElasticMigrations\Console\MigrateCommand;
use ElasticMigrations\Console\RefreshCommand;
use ElasticMigrations\Console\ResetCommand;
use ElasticMigrations\Console\RollbackCommand;
use ElasticMigrations\Console\StatusCommand;
use Illuminate\Support\ServiceProvider as AbstractServiceProvider;

final class ServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string
     */
    private $configPath;
    /**
     * @var string
     */
    private $migrationsPath;
    /**
     * @var array
     */
    public $bindings = [
        IndexManagerInterface::class => IndexManagerAdapter::class,
    ];
    /**
     * @var array
     */
    private $commands = [
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
