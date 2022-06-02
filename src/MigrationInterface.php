<?php declare(strict_types=1);

namespace Elastic\Migrations;

interface MigrationInterface
{
    public function up(): void;

    public function down(): void;
}
