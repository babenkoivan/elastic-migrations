<?php
declare(strict_types=1);

namespace ElasticMigrations\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

final class MigrationStorage
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $directory;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->directory = rtrim(config('elastic.migrations.directory'), '/');
    }

    public function create(string $fileName, string $content): ?MigrationFile
    {
        $filePath = $this->resolvePath($fileName);

        return $this->filesystem->put($filePath, $content) ? new MigrationFile($filePath) : null;
    }

    public function findByName(string $fileName): ?MigrationFile
    {
        $filePath = $this->resolvePath($fileName);

        return $this->filesystem->exists($filePath) ? new MigrationFile($filePath) : null;
    }

    public function findAll(): Collection
    {
        $files = $this->filesystem->glob($this->directory . '/*_*.php');

        return collect($files)->map(function (string $filePath) {
            return new MigrationFile($filePath);
        });
    }

    private function resolvePath(string $fileName): string
    {
        return sprintf('%s/%s.php', $this->directory, $fileName);
    }
}
