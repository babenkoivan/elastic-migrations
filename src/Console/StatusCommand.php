<?php declare(strict_types=1);

namespace Elastic\Migrations\Console;

use Elastic\Migrations\Migrator;
use Illuminate\Console\Command;

class StatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:status {--pending}';
    /**
     * @var string
     */
    protected $description = 'Show the status of each migration.';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        if (!$migrator->isReady()) {
            return 1;
        }

        $migrator->showStatus($this->option('pending', false));

        return 0;
    }
}
