<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Console;

use Illuminate\Console\Command;
use OpenSearch\Migrations\Migrator;

class StatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'opensearch:migrate:status';
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

        $migrator->showStatus();

        return 0;
    }
}
