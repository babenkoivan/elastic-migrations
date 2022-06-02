<?php declare(strict_types=1);

namespace Elastic\Migrations\Filesystem;

class MigrationFile
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function getName(): string
    {
        return basename($this->filePath, '.php');
    }

    public function getPath(): string
    {
        return $this->filePath;
    }
}
