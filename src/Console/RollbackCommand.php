<?php
declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\Migrator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

final class RollbackCommand extends Command
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
    /**
     * @var Migrator
     */
    private $migrator;

    public function __construct(Migrator $migrator)
    {
        parent::__construct();

        $this->migrator = $migrator;
    }

    public function handle()
    {
        $this->migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$this->migrator->isReady()) {
            return;
        }

        if ($fileName = $this->argument('fileName')) {
            $this->migrator->rollbackOne($fileName);
        } else {
            $this->migrator->rollbackLastBatch();
        }
    }
}
