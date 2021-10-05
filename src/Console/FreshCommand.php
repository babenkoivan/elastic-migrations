<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\IndexManagerInterface;
use ElasticMigrations\Migrator;
use ElasticMigrations\Repositories\MigrationRepository;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class FreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:fresh 
        {--force : Force the operation to run when in production}';
    /**
     * @var string
     */
    protected $description = 'Drop all indices and re-run all migrations';

    public function handle(
        Migrator $migrator,
        MigrationRepository $migrationRepository,
        IndexManagerInterface $indexManager
    ): int {
        $migrator->setOutput($this->output);

        if (! $this->confirmToProceed() || ! $migrator->isReady()) {
            return 1;
        }

        $indexManager->drop('*');

        $migrationRepository->truncate();

        $migrator->migrateAll();

        return 0;
    }
}
