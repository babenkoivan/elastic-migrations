<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\Migrator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class MigrateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate 
        {fileName? : The name of the migration file} 
        {--force : Force the operation to run when in production}';
    /**
     * @var string
     */
    protected $description = 'Run the migrations';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        if ($fileName = $this->argument('fileName')) {
            $migrator->migrateOne($fileName);
        } else {
            $migrator->migrateAll();
        }

        return 0;
    }
}
