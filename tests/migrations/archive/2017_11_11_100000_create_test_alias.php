<?php declare(strict_types=1);

use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

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
