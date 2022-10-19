<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use OpenSearch\Migrations\Migrator;

class ResetCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'opensearch:migrate:reset 
        {--force : Force the operation to run when in production.}';
    /**
     * @var string
     */
    protected $description = 'Rollback all migrations.';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        $migrator->rollbackAll();

        return 0;
    }
}
