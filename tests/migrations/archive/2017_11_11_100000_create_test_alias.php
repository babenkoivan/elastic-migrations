<?php declare(strict_types=1);

use OpenSearch\Migrations\Facades\Index;
use OpenSearch\Migrations\MigrationInterface;

final class CreateTestAlias implements MigrationInterface
{
    public function up(): void
    {
        Index::putAlias('tmp', 'test');
    }

    public function down(): void
    {
        Index::drop('tmp');
    }
}
