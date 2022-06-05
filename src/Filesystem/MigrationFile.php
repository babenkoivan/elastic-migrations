<?php declare(strict_types=1);

namespace Elastic\Migrations\Filesystem;

class MigrationFile
{
    public const FILE_EXTENSION = '.php';

    protected string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function name(): string
    {
        return basename($this->filePath, static::FILE_EXTENSION);
    }

    public function path(): string
    {
        return $this->filePath;
    }
}
