<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\IndexManagerInterface;
use ElasticMigrations\Migrator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class FreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'elastic:migrate:fresh 
        {--force : Force the operation to run when in production}';

    /**
     * @var string
     */
    protected $description = 'Drop all indicies and re-run all migrations';

    /**
     * @var Migrator
     */
    private $migrator;
    
    /**
     * @var IndexManagerInterface
     */
    private $indexManager;

    public function __construct(Migrator $migrator, IndexManagerInterface $indexManager)
    {
        parent::__construct();

        $this->migrator = $migrator;
        $this->indexManager = $indexManager;
    }

    /**
     * @return int
     */
    public function handle()
    {
        $this->migrator->setOutput($this->output);

        if (! $this->confirmToProceed() || ! $this->migrator->isReady()) {
            return 1;
        }

        $this->indexManager->dropAll();

        $this->migrator->reset();

        $this->migrator->migrateAll();

        return 0;
    }
}
