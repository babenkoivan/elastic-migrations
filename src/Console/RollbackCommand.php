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
    /**
     * @var Migrator
     */
    private $migrator;

    public function __construct(Migrator $migrator)
    {
        parent::__construct();

        $this->migrator = $migrator;
    }

    /**
     * @return int
     */
    public function handle()
    {
        $this->migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$this->migrator->isReady()) {
            return 1;
        }

        if ($fileName = $this->argument('fileName')) {
            $this->migrator->rollbackOne($fileName);
        } else {
            $this->migrator->rollbackLastBatch();
        }

        return 0;
    }
}
