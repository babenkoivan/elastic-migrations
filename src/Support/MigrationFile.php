<?php
declare(strict_types=1);

namespace ElasticMigrations\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\SplFileInfo;

final class MigrationFile
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

    public function create(string $fileName, string $content): ?string
    {
        $absolutePath = $this->makeAbsolutePath($fileName);

        return $this->filesystem->put($absolutePath, $content) ? $absolutePath : null;
    }

    public function findByName(string $fileName): ?string
    {
        $absolutePath = $this->makeAbsolutePath($fileName);

        return $this->filesystem->exists($absolutePath) ? $absolutePath : null;
    }

    public function findAll(): Collection
    {
        $files = $this->filesystem->files($this->directory);

        return collect($files)->map(function (SplFileInfo $fileInfo) {
            return $fileInfo->getPathname();
        });
    }

    private function makeAbsolutePath(string $fileName): string
    {
        $fileName = str_replace('.php', '', $fileName);

        return sprintf('%s/%s.php', $this->directory, $fileName);
    }
}
