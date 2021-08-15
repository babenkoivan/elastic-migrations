<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use ElasticMigrations\IndexManagerInterface;
use ElasticMigrations\Migrator;
use ElasticMigrations\Repositories\MigrationRepository;
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
    protected $description = 'Drop all indices and re-run all migrations';
    /**
     * @var Migrator
     */
    private $migrator;
    /**
     * @var MigrationRepository
     */
    private $migrationRepository;
    /**
     * @var IndexManagerInterface
     */
    private $indexManager;

    public function __construct(
        Migrator $migrator,
        MigrationRepository $migrationRepository,
        IndexManagerInterface $indexManager
    ) {
        parent::__construct();

        $this->migrator = $migrator;
        $this->migrationRepository = $migrationRepository;
        $this->indexManager = $indexManager;
    }

    public function handle(): int
    {
        $this->migrator->setOutput($this->output);

        if (!$this->confirmToProceed() || !$this->migrator->isReady()) {
            return 1;
        }

        $this->indexManager->drop('*');

        $this->migrationRepository->truncate();

        $this->migrator->migrateAll();

        return 0;
    }
}
