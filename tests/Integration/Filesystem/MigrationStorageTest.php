<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration\Filesystem;

use Carbon\Carbon;
use Elastic\Migrations\Filesystem\MigrationFile;
use Elastic\Migrations\Filesystem\MigrationStorage;
use Elastic\Migrations\Tests\Integration\TestCase;

/**
 * @covers \Elastic\Migrations\Filesystem\MigrationStorage
 */
final class MigrationStorageTest extends TestCase
{
    private MigrationStorage $migrationStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationStorage = resolve(MigrationStorage::class);
    }

    public function newFileNameProvider(): array
    {
        return [
            ['2022_06_01_223400_create_new_index'],
            ['2022_06_01_223400_create_new_index.php'],
            [__DIR__ . '/../../migrations/2022_06_01_223400_create_new_index'],
        ];
    }

    public function existingFileNameProvider(): array
    {
        return [
            ['2018_12_01_081000_create_test_index'],
            ['2019_08_10_142230_update_test_index_mapping'],
            ['2019_08_10_142230_update_test_index_mapping.php'],
            [__DIR__ . '/../../migrations/2019_08_10_142230_update_test_index_mapping.php'],
        ];
    }

    public function nonExistingFileNameProvider(): array
    {
        return [
            ['3030_01_01_000000_non_existing_file.php'],
            ['3030_01_01_000000_non_existing_file'],
            ['test'],
            [''],
        ];
    }

    /**
     * @dataProvider newFileNameProvider
     */
    public function test_file_can_be_created(string $fileName): void
    {
        $file = $this->migrationStorage->create($fileName, 'content');

        $this->assertFileExists($file->path());
        $this->assertStringEqualsFile($file->path(), 'content');

        @unlink($file->path());
    }

    public function test_directory_is_created_along_with_file(): void
    {
        $baseDirectory = realpath(__DIR__ . '/../..');

        $firstLevelDirectory = $baseDirectory . '/tmp';
        $secondLevelDirectory = $firstLevelDirectory . '/migrations';

        $this->config->set('elastic.migrations.storage.default_path', $secondLevelDirectory);

        // create a new instance to apply the new config
        $file = resolve(MigrationStorage::class)->create('test', 'content');

        $this->assertDirectoryExists($secondLevelDirectory);

        @unlink($file->path());
        @rmdir($secondLevelDirectory);
        @rmdir($firstLevelDirectory);
    }

    /**
     * @dataProvider existingFileNameProvider
     */
    public function test_file_can_be_retrieved_if_exists(string $fileName): void
    {
        /** @var MigrationFile $file */
        $file = $this->migrationStorage->whereName($fileName);

        $this->assertSame(basename(trim($fileName), MigrationFile::FILE_EXTENSION), $file->name());
    }

    /**
     * @dataProvider nonExistingFileNameProvider
     */
    public function test_file_can_not_be_retrieved_if_it_does_not_exist(string $fileName): void
    {
        $file = $this->migrationStorage->whereName($fileName);

        $this->assertNull($file);
    }

    public function test_all_files_within_migrations_directory_can_be_retrieved(): void
    {
        $files = $this->migrationStorage->all();

        $this->assertSame(
            [
                '2018_12_01_081000_create_test_index',
                '2019_08_10_142230_update_test_index_mapping',
            ],
            $files->map(static fn (MigrationFile $file) => $file->name())->toArray()
        );
    }

    public function test_storage_is_ready_when_directory_exists(): void
    {
        $this->assertTrue($this->migrationStorage->isReady());
    }

    public function test_storage_is_not_ready_when_directory_does_not_exist(): void
    {
        $this->config->set('elastic.migrations.storage.default_path', '/non_existing_directory');

        // create a new instance to apply the new config
        $this->assertFalse(resolve(MigrationStorage::class)->isReady());
    }
}
