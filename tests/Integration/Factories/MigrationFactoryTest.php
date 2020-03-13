<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Factories;

use ElasticMigrations\Factories\MigrationFactory;
use ElasticMigrations\Filesystem\MigrationStorage;
use ElasticMigrations\MigrationInterface;
use ElasticMigrations\Tests\Integration\TestCase;

/**
 * @covers \ElasticMigrations\Factories\MigrationFactory
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

    public function fileNameProvider(): array
    {
        return [
            ['2018_12_01_081000_create_test_index'],
            ['2019_08_10_142230_update_test_index_mapping'],
        ];
    }

    /**
     * @dataProvider fileNameProvider
     */
    public function test_migration_can_be_created_by_file(string $fileName): void
    {
        $file = resolve(MigrationStorage::class)->findByName($fileName);

        $this->assertInstanceOf(
            MigrationInterface::class,
            $this->migrationFactory->makeByFile($file)
        );
    }
}
