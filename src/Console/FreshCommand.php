<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use OpenSearch\Migrations\IndexManagerInterface;
use OpenSearch\Migrations\Migrator;
use OpenSearch\Migrations\Repositories\MigrationRepository;

class FreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'opensearch:migrate:fresh 
        {--force : Force the operation to run when in production.}';
    /**
     * @var string
     */
    protected $description = 'Drop all indices and re-run all migrations.';

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
