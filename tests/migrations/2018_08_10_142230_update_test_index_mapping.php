<?php
declare(strict_types=1);

use ElasticAdapter\Indices\Mapping;
use ElasticMigrations\Facades\Index;
use ElasticMigrations\MigrationInterface;

final class UpdateTestIndexMapping implements MigrationInterface
{
    public function up(): void
    {
        Index::putMapping('test', function (Mapping $mapping) {
            $mapping->text('title');
        });
    }

    public function down(): void
    {
        //
    }
}
