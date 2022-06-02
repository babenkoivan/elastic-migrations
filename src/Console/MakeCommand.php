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

    public function handle(Filesystem $filesystem, MigrationStorage $migrationStorage): int
    {
        /** @var string $name */
        $name = $this->argument('name');
        $fileName = sprintf('%s_%s', (new Carbon())->format('Y_m_d_His'), Str::snake(trim($name)));
        $className = Str::studly(trim($name));

        $stub = $filesystem->get(__DIR__ . '/stubs/migration.blank.stub');
        $content = str_replace('DummyClass', $className, $stub);

        $migrationStorage->create($fileName, $content);

        $this->output->writeln('<info>Created migration:</info> ' . $fileName);

        return 0;
    }
}
