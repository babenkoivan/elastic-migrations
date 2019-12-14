<?php
declare(strict_types=1);

use ElasticMigrations\Facades\Index;
use ElasticMigrations\MigrationInterface;

final class CreateTestIndex implements MigrationInterface
{
    public function up(): void
    {
        Index::create('test');
    }

    public function down(): void
    {
        Index::drop('test');
    }
}
