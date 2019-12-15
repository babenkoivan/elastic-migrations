<?php
declare(strict_types=1);

namespace ElasticMigrations\Factories;

use ElasticMigrations\Filesystem\MigrationFile;
use ElasticMigrations\MigrationInterface;
use Illuminate\Support\Str;

final class MigrationFactory
{
    public function makeByFile(MigrationFile $file): MigrationInterface
    {
        require_once $file->getPath();

        $className = Str::studly(implode('_', array_slice(explode('_', $file->getName()), 4)));

        return new $className;
    }
}
