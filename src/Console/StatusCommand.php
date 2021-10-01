<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\Migrator;
use Illuminate\Console\Command;

class StatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:status';
    /**
     * @var string
     */
    protected $description = 'Show the status of each migration';

    /**
     * @return int
     */
    public function handle(Migrator $migrator)
    {
        $migrator->setOutput($this->output);

        if (!$migrator->isReady()) {
            return 1;
        }

        $migrator->showStatus();

        return 0;
    }
}
