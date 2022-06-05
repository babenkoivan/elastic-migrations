<?php declare(strict_types=1);

namespace Elastic\Migrations\Console;

use Elastic\Migrations\IndexManagerInterface;
use Elastic\Migrations\Migrator;
use Elastic\Migrations\Repositories\MigrationRepository;
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

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        $indexManager->drop('*');
        $migrationRepository->purge();
        $migrator->migrateAll();

        return 0;
    }
}
