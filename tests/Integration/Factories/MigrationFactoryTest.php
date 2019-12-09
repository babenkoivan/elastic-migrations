<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Factories;

use ElasticMigrations\Factories\MigrationFactory;
use ElasticMigrations\MigrationInterface;
use ElasticMigrations\Support\MigrationFile;
use ElasticMigrations\Tests\Integration\TestCase;

/**
 * @covers \ElasticMigrations\Factories\MigrationFactory
 * @uses   \ElasticMigrations\ServiceProvider
 * @uses   \ElasticMigrations\Support\MigrationFile
 */
final class MigrationFactoryTest extends TestCase
{
    /**
     * @var MigrationFactory
     */
    private $migrationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationFactory = resolve(MigrationFactory::class);
    }

    public function migrationFileNameProvider(): array
    {
        return [
            ['2019_12_01_081000_create_test_index.php'],
            ['2018_08_10_142230_update_test_index_mapping.php'],
        ];
    }

    /**
     * @dataProvider migrationFileNameProvider
     */
    public function test_migration_can_be_created_by_file_path(string $fileName): void
    {
        $absolutePath = resolve(MigrationFile::class)->findByName($fileName);

        $this->assertInstanceOf(
            MigrationInterface::class,
            $this->migrationFactory->makeByPath($absolutePath)
        );
    }
}
