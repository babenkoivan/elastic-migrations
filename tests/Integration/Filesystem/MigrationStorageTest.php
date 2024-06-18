<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration\Filesystem;

use Elastic\Migrations\Filesystem\MigrationFile;
use Elastic\Migrations\Filesystem\MigrationStorage;
use Elastic\Migrations\Tests\Integration\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(MigrationStorage::class)]
final class MigrationStorageTest extends TestCase
{
    private MigrationStorage $migrationStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationStorage = resolve(MigrationStorage::class);
        $this->migrationStorage->registerPaths([__DIR__ . '/../../migrations/archive']);
    }

    #[TestWith(['2022_06_01_223400_create_new_index'])]
    #[TestWith([__DIR__ . '/../../migrations/archive/2022_06_01_223400_create_new_index.php'])]
    public function test_file_can_be_created(string $fileName): void
    {
        $file = $this->migrationStorage->create($fileName, 'content');

        $this->assertFileExists($file->path());
        $this->assertStringEqualsFile($file->path(), 'content');

        @unlink($file->path());
    }

    public function test_directory_is_created_along_with_file(): void
    {
        $defaultPath = __DIR__ . '/../../migrations/tmp';
        $this->config->set('elastic.migrations.storage.default_path', $defaultPath);

        // create a new instance to apply the new config
        $this->app->forgetInstance(MigrationStorage::class);
        $migrationStorage = resolve(MigrationStorage::class);

        $file = $migrationStorage->create('test', 'content');

        $this->assertDirectoryExists($defaultPath);

        @unlink($file->path());
        @rmdir($defaultPath);
    }

    #[TestWith(['2018_12_01_081000_create_test_index'])]
    #[TestWith(['2019_08_10_142230_update_test_index_mapping'])]
    #[TestWith([__DIR__ . '/../../migrations/archive/2017_11_11_100000_create_test_alias.php'])]
    public function test_file_can_be_retrieved_if_exists(string $fileName): void
    {
        /** @var MigrationFile $file */
        $file = $this->migrationStorage->whereName($fileName);

        $this->assertSame(basename($fileName, MigrationFile::FILE_EXTENSION), $file->name());
    }

    #[TestWith(['3030_01_01_000000_non_existing_file'])]
    #[TestWith(['test'])]
    #[TestWith([''])]
    #[TestWith([__DIR__ . '/../../migrations/archive/3030_01_01_000000_non_existing_file.php'])]
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
                '2017_11_11_100000_create_test_alias',
                '2018_12_01_081000_create_test_index',
                '2019_08_10_142230_update_test_index_mapping',
            ],
            $files->map(static fn (MigrationFile $file) => $file->name())->toArray()
        );
    }

    public function test_storage_is_ready_when_default_path_exists(): void
    {
        $this->assertTrue($this->migrationStorage->isReady());
    }

    public function test_storage_is_not_ready_when_default_path_does_not_exist(): void
    {
        $this->config->set('elastic.migrations.storage.default_path', '/non_existing_directory');

        // create a new instance to apply the new config
        $this->app->forgetInstance(MigrationStorage::class);
        $migrationStorage = resolve(MigrationStorage::class);

        $this->assertFalse($migrationStorage->isReady());
    }
}
