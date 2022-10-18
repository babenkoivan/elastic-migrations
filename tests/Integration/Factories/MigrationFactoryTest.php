<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Tests\Integration\Factories;

use OpenSearch\Migrations\Factories\MigrationFactory;
use OpenSearch\Migrations\Filesystem\MigrationFile;
use OpenSearch\Migrations\Filesystem\MigrationStorage;
use OpenSearch\Migrations\MigrationInterface;
use OpenSearch\Migrations\Tests\Integration\TestCase;

/**
 * @covers \OpenSearch\Migrations\Factories\MigrationFactory
 */
final class MigrationFactoryTest extends TestCase
{
    private MigrationFactory $migrationFactory;
    private MigrationStorage $migrationStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationFactory = resolve(MigrationFactory::class);
        $this->migrationStorage = resolve(MigrationStorage::class);
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
    public function test_migration_can_be_created_from_file(string $fileName): void
    {
        /** @var MigrationFile $file */
        $file = $this->migrationStorage->whereName($fileName);

        $this->assertInstanceOf(
            MigrationInterface::class,
            $this->migrationFactory->makeFromFile($file)
        );
    }
}
