<?php declare(strict_types=1);

use ElasticAdapter\Indices\Mapping;
use ElasticMigrations\Facades\Index;
use ElasticMigrations\MigrationInterface;

final class UpdateAnotherTestIndexMapping implements MigrationInterface
{
    public function up(): void
    {
        Index::putMapping('another_test', static function (Mapping $mapping) {
            $mapping->enableSource();
            $mapping->text('title');
        });
    }

    public function down(): void
    {
        Index::putMapping('another_test', static function (Mapping $mapping) {
            $mapping->disableSource();
        });
    }
}
