<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Filesystem;

use Carbon\Carbon;
use ElasticMigrations\Filesystem\MigrationFile;
use ElasticMigrations\Filesystem\MigrationStorage;
use ElasticMigrations\Tests\Integration\TestCase;

/**
 * @covers \ElasticMigrations\Filesystem\MigrationStorage
 * @uses   \ElasticMigrations\Filesystem\MigrationFile
 * @uses   \ElasticMigrations\Filesystem\MigrationStorage
 * @uses   \ElasticMigrations\ServiceProvider
 */
final class MigrationStorageTest extends TestCase
{
    /**
     * @var MigrationStorage
     */
    private $migrationStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationStorage = resolve(MigrationStorage::class);
    }

    public function test_file_can_be_created(): void
    {
        $fileName = sprintf(
            '%s_create_tmp_%s_index',
            (new Carbon())->format('Y_m_d_His'),
            uniqid()
        );

        $file = $this->migrationStorage->create($fileName, 'content');

        $this->assertSame($fileName, $file->getName());
        $this->assertFileExists($file->getPath());
        $this->assertStringEqualsFile($file->getPath(), 'content');

        @unlink($file->getPath());
    }

    public function existingFileNameProvider(): array
    {
        return [
            ['2018_12_01_081000_create_test_index'],
            ['2019_08_10_142230_update_test_index_mapping'],
        ];
    }

    /**
     * @dataProvider existingFileNameProvider
     */
    public function test_file_can_be_found_if_exists(string $fileName): void
    {
        $file = $this->migrationStorage->findByName($fileName);

        $this->assertSame(basename($fileName, '.php'), $file->getName());
    }

    public function nonExistingFileNameProvider(): array
    {
        return [
            ['3030_01_01_000000_non_existing_file.php'],
            ['test'],
            [''],
        ];
    }

    /**
     * @dataProvider nonExistingFileNameProvider
     */
    public function test_file_can_not_be_found_if_does_not_exist(string $fileName): void
    {
        $file = $this->migrationStorage->findByName($fileName);

        $this->assertNull($file);
    }

    public function test_all_files_within_migrations_directory_can_be_retrieved(): void
    {
        $files = $this->migrationStorage->findAll();

        $this->assertSame(
            [
                '2018_12_01_081000_create_test_index',
                '2019_08_10_142230_update_test_index_mapping',
            ],
            $files->map(function (MigrationFile $file) {
                return $file->getName();
            })->toArray()
        );
    }
}
