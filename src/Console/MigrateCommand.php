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
            $this->migrator->migrateOne($fileName);
        } else {
            $this->migrator->migrateAll();
        }

        return 0;
    }
}
