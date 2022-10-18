<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use OpenSearch\Migrations\Filesystem\MigrationStorage;

class MakeCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'opensearch:make:migration 
        {name : Name of the migration or a full path to the new migration file.}';
    /**
     * @var string
     */
    protected $description = 'Create a new migration file.';

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
