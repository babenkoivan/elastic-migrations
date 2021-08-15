<?php declare(strict_types=1);

namespace ElasticMigrations\Console;

use Carbon\Carbon;
use ElasticMigrations\Filesystem\MigrationStorage;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'elastic:make:migration 
        {name : The name of the migration}';
    /**
     * @var string
     */
    protected $description = 'Create a new migration file';
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var MigrationStorage
     */
    private $migrationStorage;

    public function __construct(Filesystem $filesystem, MigrationStorage $migrationStorage)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->migrationStorage = $migrationStorage;
    }

    public function handle(): int
    {
        $name = Str::snake(trim($this->argument('name')));

        $fileName = sprintf('%s_%s', (new Carbon())->format('Y_m_d_His'), $name);
        $className = Str::studly($name);

        $stub = $this->filesystem->get(__DIR__ . '/stubs/migration.blank.stub');
        $content = str_replace('DummyClass', $className, $stub);

        $this->migrationStorage->create($fileName, $content);

        $this->output->writeln('<info>Created migration:</info> ' . $fileName);

        return 0;
    }
}
