<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\Migrator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class ResetCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:reset 
        {--force : Force the operation to run when in production}';
    /**
     * @var string
     */
    protected $description = 'Rollback all migrations';

    /**
     * @return int
     */
    public function handle(Migrator $migrator)
    {
        $migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        $migrator->rollbackAll();

        return 0;
    }
}
