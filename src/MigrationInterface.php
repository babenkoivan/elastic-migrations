<?php declare(strict_types=1);

namespace OpenSearch\Migrations;

interface MigrationInterface
{
    public function up(): void;

    public function down(): void;
}
