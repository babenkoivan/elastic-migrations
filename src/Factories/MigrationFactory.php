<?php declare(strict_types=1);

namespace Elastic\Migrations\Factories;

use Elastic\Migrations\Filesystem\MigrationFile;
use Elastic\Migrations\MigrationInterface;
use Illuminate\Support\Str;

class MigrationFactory
{
    public function makeFromFile(MigrationFile $file): MigrationInterface
    {
        $class = require_once $file->path();

        if (is_object($class)) {
            return $class;
        }

        $className = Str::studly(implode('_', array_slice(explode('_', $file->name()), 4)));
        /** @var MigrationInterface $migration */
        $migration = resolve($className);

        return $migration;
    }
}
