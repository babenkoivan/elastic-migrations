<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use OpenSearch\Migrations\Migrator;

class MigrateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'opensearch:migrate 
        {name? : Name of the migration or a full path to the existing migration file.}
        {--force : Force the operation to run when in production.}';
    /**
     * @var string
     */
    protected $description = 'Run the migrations.';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$migrator->isReady()) {
            return 1;
        }

        /** @var ?string $name */
        $name = $this->argument('name');

        if (isset($name)) {
            $migrator->migrateOne(trim($name));
        } else {
            $migrator->migrateAll();
        }

        return 0;
    }
}
