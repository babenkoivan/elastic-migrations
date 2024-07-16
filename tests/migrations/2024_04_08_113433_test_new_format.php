<?php
declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

return new class implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        //
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        //
    }
};
