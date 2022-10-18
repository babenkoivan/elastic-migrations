<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Filesystem;

use const DIRECTORY_SEPARATOR;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use OpenSearch\Migrations\ReadinessInterface;

class MigrationStorage implements ReadinessInterface
{
    protected const DIRECTORY_PERMISSIONS = 0755;

    protected Filesystem $filesystem;
    protected string $defaultPath;
    protected Collection $paths;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->defaultPath = config('opensearch.migrations.storage.default_path', '');
        $this->paths = collect([$this->defaultPath]);
    }

    public function create(string $fileName, string $content): MigrationFile
    {
        if ($this->isPath($fileName)) {
            $this->filesystem->put($fileName, $content);
            return new MigrationFile($fileName);
        }

        if (!$this->filesystem->isDirectory($this->defaultPath)) {
            $this->filesystem->makeDirectory($this->defaultPath, static::DIRECTORY_PERMISSIONS, true);
        }

        $filePath = $this->makeFilePath($this->defaultPath, $fileName);
        $this->filesystem->put($filePath, $content);
        return new MigrationFile($filePath);
    }

    public function whereName(string $fileName): ?MigrationFile
    {
        if ($this->isPath($fileName)) {
            return $this->filesystem->exists($fileName) ? new MigrationFile($fileName) : null;
        }

        foreach ($this->paths as $path) {
            $filePath = $this->makeFilePath($path, $fileName);

            if ($this->filesystem->exists($filePath)) {
                return new MigrationFile($filePath);
            }
        }

        return null;
    }

    public function all(): Collection
    {
        return $this->paths->flatMap(
            fn (string $path) => $this->filesystem->glob($path . '/*_*' . MigrationFile::FILE_EXTENSION)
        )->filter()->mapWithKeys(
            static function (string $filePath) {
                $file = new MigrationFile($filePath);
                return [$file->name() => $file];
            }
        )->sortKeys()->values();
    }

    public function registerPaths(array $paths): self
    {
        $this->paths = $this->paths->merge($paths)->filter()->unique()->values();
        return $this;
    }

    public function isReady(): bool
    {
        return $this->filesystem->isDirectory($this->defaultPath);
    }

    private function isPath(string $path): bool
    {
        return strpos($path, DIRECTORY_SEPARATOR) !== false;
    }

    private function makeFilePath(string $path, string $fileName): string
    {
        return $path . DIRECTORY_SEPARATOR . $fileName . MigrationFile::FILE_EXTENSION;
    }
}
