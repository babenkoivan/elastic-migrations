<?php declare(strict_types=1);

namespace ElasticMigrations\Filesystem;

use ElasticMigrations\ReadinessInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class MigrationStorage implements ReadinessInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $mainDirectory;
    /**
     * @var array
     */
    private $directories = [];

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->mainDirectory = rtrim(config('elastic.migrations.storage_directory', ''), '/');
        $this->directories[] = $this->mainDirectory;
    }

    public function addDirectory(string $directory): void
    {
        $this->directories = array_unique(array_merge($this->directories, [$directory]));
    }

    public function create(string $fileName, string $content): MigrationFile
    {
        if (!$this->filesystem->isDirectory($this->mainDirectory)) {
            $this->filesystem->makeDirectory($this->mainDirectory, 0755, true);
        }

        $filePath = $this->resolvePath($fileName, $this->mainDirectory);
        $this->filesystem->put($filePath, $content);

        return new MigrationFile($filePath);
    }

    public function findByName(string $fileName): ?MigrationFile
    {
        foreach($this->directories as $directory) {
            $filePath = $this->resolvePath($fileName, $directory);

            if ($this->filesystem->exists($filePath)) {
                return new MigrationFile($filePath);
            }
        }

        return null;
    }

    public function findAll(): Collection
    {
        $files = [];
        foreach ($this->directories as $directory) {
            $files[] = $this->filesystem->glob($directory . '/*_*.php');
        }
        $files = array_merge([], ...$files);

        return collect($files)->sort()->values()->map(static function (string $filePath) {
            return new MigrationFile($filePath);
        });
    }

    private function resolvePath(string $fileName, string $directory): string
    {
        return sprintf('%s/%s.php', $directory, str_replace('.php', '', trim($fileName)));
    }

    public function isReady(): bool
    {
        return $this->filesystem->isDirectory($this->mainDirectory);
    }
}
