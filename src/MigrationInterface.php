<?php declare(strict_types=1);

namespace ElasticMigrations;

interface MigrationInterface
{
    public function up(): void;

    public function down(): void;
}
