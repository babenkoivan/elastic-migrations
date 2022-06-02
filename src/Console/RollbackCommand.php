<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\Migrator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class RollbackCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:rollback 
        {fileName? : The name of the migration file}
        {--force : Force the operation to run when in production}';
    /**
     * @var string
     */
    protected $description = 'Rollback migrations';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        /** @var ?string $fileName */
        $fileName = $this->argument('fileName');

        if (isset($fileName)) {
            $migrator->rollbackOne(trim($fileName));
        } else {
            $migrator->rollbackLastBatch();
        }

        return 0;
    }
}
