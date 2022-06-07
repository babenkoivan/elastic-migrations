<?php declare(strict_types=1);

namespace Elastic\Migrations\Console;

use Elastic\Migrations\Migrator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class RollbackCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:rollback 
        {name? : The name of the migration or a full path to the existing migration file.}
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
