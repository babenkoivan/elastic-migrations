<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration;

use ElasticMigrations\Facades\Index;
use ElasticMigrations\Migrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @covers \ElasticMigrations\Migrator
 * @uses   \ElasticMigrations\Filesystem\MigrationFile
 * @uses   \ElasticMigrations\Filesystem\MigrationStorage
 * @uses   \ElasticMigrations\Factories\MigrationFactory
 * @uses   \ElasticMigrations\Adapters\IndexManagerAdapter
 * @uses   \ElasticMigrations\Repositories\MigrationRepository
 * @uses   \ElasticMigrations\Facades\Index
 * @uses   \ElasticMigrations\ServiceProvider
 */
final class MigratorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    private $table;
    /**
     * @var MockInterface
     */
    private $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = config('elastic.migrations.table');
        $this->output = $this->mock(StyleInterface::class);

        // create fixtures
        DB::table($this->table)->insert([
            ['migration' => '2018_12_01_081000_create_test_index', 'batch' => 1],
        ]);
    }

    public function test_single_migration_can_not_be_executed_if_file_does_not_exist(): void
    {
        $this->output
            ->shouldReceive('error')
            ->once()
            ->with('Migration is not found: 3020_11_01_045023_drop_test_index');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->migrateOne('3020_11_01_045023_drop_test_index'));
    }

    public function test_single_migration_can_be_executed_if_file_exists(): void
    {
        Index::shouldReceive('putMapping')->once();

        $this->output
            ->shouldReceive('note')
            ->with('Migrating: 2019_08_10_142230_update_test_index_mapping');

        $this->output
            ->shouldReceive('success')
            ->with('Migrated: 2019_08_10_142230_update_test_index_mapping');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->migrateOne('2019_08_10_142230_update_test_index_mapping'));

        $this->assertDatabaseHas($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 2
        ]);
    }

    public function test_all_migrations_can_not_be_executed_if_directory_is_empty(): void
    {
        // create a temporary empty directory and reconfigure the package to use it
        $tmpDirectory = config('elastic.migrations.storage_directory') . '/tmp';

        @mkdir($tmpDirectory);
        $this->app['config']->set('elastic.migrations.storage_directory', $tmpDirectory);

        // check that there is nothing to migrate
        $this->output
            ->shouldReceive('warning')
            ->once()
            ->with('Nothing to migrate');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->migrateAll());

        // remove the temporary directory
        @rmdir($tmpDirectory);
    }

    public function test_all_migrations_can_be_executed_if_directory_is_not_empty(): void
    {
        Index::shouldReceive('putMapping')->once();

        $this->output
            ->shouldReceive('note')
            ->with('Migrating: 2019_08_10_142230_update_test_index_mapping');

        $this->output
            ->shouldReceive('success')
            ->with('Migrated: 2019_08_10_142230_update_test_index_mapping');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->migrateAll());

        $this->assertDatabaseHas($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 2
        ]);
    }

    public function test_single_migration_can_not_be_rolled_back_if_file_does_not_exist(): void
    {
        $this->output
            ->shouldReceive('error')
            ->once()
            ->with('Migration is not found: 3020_11_01_045023_drop_test_index');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->rollbackOne('3020_11_01_045023_drop_test_index'));
    }

    public function test_single_migration_can_not_be_rolled_back_if_file_is_not_yet_migrated(): void
    {
        $this->output
            ->shouldReceive('error')
            ->once()
            ->with('Migration is not yet migrated: 2019_08_10_142230_update_test_index_mapping');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->rollbackOne('2019_08_10_142230_update_test_index_mapping'));
    }

    public function test_single_migration_can_be_rolled_back_if_file_exists_and_is_migrated(): void
    {
        Index::shouldReceive('drop')->once();

        $this->output
            ->shouldReceive('note')
            ->with('Rolling back: 2018_12_01_081000_create_test_index');

        $this->output
            ->shouldReceive('success')
            ->with('Rolled back: 2018_12_01_081000_create_test_index');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->rollbackOne('2018_12_01_081000_create_test_index'));

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2018_12_01_081000_create_test_index',
            'batch' => 1
        ]);
    }

    public function test_last_batch_can_not_be_rolled_back_if_some_files_are_missing(): void
    {
        // imitate, that migration has already been migrated
        DB::table($this->table)->insert([
            ['migration' => '2019_03_10_101500_create_test_index', 'batch' => 2],
        ]);

        $this->output
            ->shouldReceive('error')
            ->once()
            ->with('Migration is not found: 2019_03_10_101500_create_test_index');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->rollbackLastBatch());
    }

    public function test_last_batch_can_be_rolled_back_if_all_files_are_present(): void
    {
        // imitate, that migration has already been migrated
        DB::table($this->table)->insert([
            ['migration' => '2019_08_10_142230_update_test_index_mapping', 'batch' => 4],
        ]);

        Index::shouldReceive('putMapping')->once();

        $this->output
            ->shouldReceive('note')
            ->with('Rolling back: 2019_08_10_142230_update_test_index_mapping');

        $this->output
            ->shouldReceive('success')
            ->with('Rolled back: 2019_08_10_142230_update_test_index_mapping');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->rollbackLastBatch());

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 4
        ]);
    }

    public function test_all_migrations_can_not_be_rolled_back_if_some_files_are_missing(): void
    {
        // imitate, that migrations have already been migrated
        DB::table($this->table)->insert([
            ['migration' => '2019_03_10_101500_create_test_index', 'batch' => 2],
            ['migration' => '2019_01_01_053550_drop_test_index', 'batch' => 2],
        ]);

        $this->output
            ->shouldReceive('error')
            ->once()
            ->with('Migration is not found: 2019_01_01_053550_drop_test_index,2019_03_10_101500_create_test_index');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->rollbackAll());
    }

    public function test_all_migrations_can_be_rolled_back_if_all_files_are_present(): void
    {
        // imitate, that migration has already been migrated
        DB::table($this->table)->insert([
            ['migration' => '2019_08_10_142230_update_test_index_mapping', 'batch' => 2],
        ]);

        Index::shouldReceive('putMapping')->once();
        Index::shouldReceive('drop')->once();

        $this->output
            ->shouldReceive('note')
            ->with('Rolling back: 2019_08_10_142230_update_test_index_mapping');

        $this->output
            ->shouldReceive('success')
            ->with('Rolled back: 2019_08_10_142230_update_test_index_mapping');

        $this->output
            ->shouldReceive('note')
            ->with('Rolling back: 2018_12_01_081000_create_test_index');

        $this->output
            ->shouldReceive('success')
            ->with('Rolled back: 2018_12_01_081000_create_test_index');

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->rollbackAll());

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 2
        ]);

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2018_12_01_081000_create_test_index',
            'batch' => 1
        ]);
    }

    public function test_status_is_displayed_correctly(): void
    {
        $this->output
            ->shouldReceive('table')
            ->with(
                ['Ran?', 'Last batch?', 'Migration'],
                [
                    ['Yes', 'Yes', '2018_12_01_081000_create_test_index'],
                    ['No', 'No', '2019_08_10_142230_update_test_index_mapping'],
                ]
            );

        $migrator = $this->makeMigrator();

        $this->assertSame($migrator, $migrator->showStatus());
    }

    private function makeMigrator(): Migrator
    {
        return resolve(Migrator::class)->setOutput($this->output);
    }
}
