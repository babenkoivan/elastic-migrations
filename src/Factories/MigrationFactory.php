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
            // This is an annonymous class so return it directly
            return $class;
        }

        try {
            $className = Str::studly(implode('_', array_slice(explode('_', $file->name()), 4)));
            /** @var MigrationInterface $migration */
            $migration = resolve($className);
        } catch (\Illuminate\Contracts\Container\BindingResolutionException) {
            // This is probably an annonymous class so include and return it directly
            // This is needed for when the migration already has been included and require_once
            // therefore just returns 'true' insead of the resolved object
            return require $file->path();
        }

        return $migration;
    }
}
