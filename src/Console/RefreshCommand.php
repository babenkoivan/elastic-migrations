<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\Migrator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class RefreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:refresh 
        {--force : Force the operation to run when in production}';
    /**
     * @var string
     */
    protected $description = 'Reset and re-run all migrations';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        $migrator->rollbackAll();
        $migrator->migrateAll();

        return 0;
    }
}
