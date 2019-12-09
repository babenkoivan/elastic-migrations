<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Support;

use Carbon\Carbon;
use ElasticMigrations\Support\MigrationFile;
use ElasticMigrations\Tests\Integration\TestCase;
use Illuminate\Support\Str;

/**
 * @covers \ElasticMigrations\Support\MigrationFile
 * @uses   \ElasticMigrations\ServiceProvider
 */
final class MigrationFileTest extends TestCase
{
    /**
     * @var MigrationFile
     */
    private $migrationFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationFile = resolve(MigrationFile::class);
    }

    public function test_file_can_be_created(): void
    {
        $fileName = sprintf(
            '%s_create_tmp_%s_index.php',
            (new Carbon())->format('Y_m_d_His'),
            uniqid()
        );

        $absolutePath = $this->migrationFile->create($fileName, 'content');

        $this->assertSame($fileName, basename($absolutePath));
        $this->assertFileExists($absolutePath);
        $this->assertStringEqualsFile($absolutePath, 'content');

        @unlink($absolutePath);
    }

    public function existingMigrationFileNameProvider(): array
    {
        return [
            ['2019_12_01_081000_create_test_index'],
            ['2019_12_01_081000_create_test_index.php'],
            ['2018_08_10_142230_update_test_index_mapping.php'],
        ];
    }

    /**
     * @dataProvider existingMigrationFileNameProvider
     */
    public function test_file_can_be_found_if_exists(string $fileName): void
    {
        $absolutePath = $this->migrationFile->findByName($fileName);

        $this->assertSame(
            Str::endsWith($fileName, '.php') ? $fileName : $fileName . '.php',
            basename($absolutePath)
        );
    }

    public function nonExistingMigrationFileNameProvider(): array
    {
        return [
            ['3030_01_01_000000_non_existing_file.php'],
            ['test'],
            [''],
        ];
    }

    /**
     * @dataProvider nonExistingMigrationFileNameProvider
     */
    public function test_file_cant_be_found_if_doesnt_exist(string $fileName): void
    {
        $absolutePath = $this->migrationFile->findByName($fileName);

        $this->assertNull($absolutePath);
    }

    public function test_all_files_within_migrations_directory_can_be_retrieved(): void
    {
        $absolutePaths = $this->migrationFile->findAll();

        $this->assertSame(
            [
                '2018_08_10_142230_update_test_index_mapping.php',
                '2019_12_01_081000_create_test_index.php',
            ],
            $absolutePaths->map('basename')->toArray()
        );
    }
}
