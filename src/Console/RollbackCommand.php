<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use OpenSearch\Migrations\Migrator;

class RollbackCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'opensearch:migrate:rollback 
        {name? : Name of the migration or a full path to the existing migration file.}
        {--force : Force the operation to run when in production.}';
    /**
     * @var string
     */
    protected $description = 'Rollback migrations.';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        /** @var ?string $name */
        $name = $this->argument('name');

        if (isset($name)) {
            $migrator->rollbackOne(trim($name));
        } else {
            $migrator->rollbackLastBatch();
        }

        return 0;
    }
}
