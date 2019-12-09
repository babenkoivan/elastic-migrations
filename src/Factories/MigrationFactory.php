<?php
declare(strict_types=1);

namespace ElasticMigrations\Factories;

use ElasticMigrations\MigrationInterface;
use Illuminate\Support\Str;

final class MigrationFactory
{
    public function makeByPath(string $filePath): MigrationInterface
    {
        require_once $filePath;

        $baseFileName = basename($filePath, '.php');
        $className = Str::studly(implode('_', array_slice(explode('_', $baseFileName), 4)));

        return new $className;
    }
}
