<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration;

use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\Filesystem\MigrationStorage;
use Elastic\Migrations\Migrator;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Elastic\Migrations\Migrator
 */
final class MigratorTest extends TestCase
{
    use RefreshDatabase;

    private string $table;
    private MockObject $output;
    private Migrator $migrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = $this->config->get('elastic.migrations.database.table');
        $this->output = $this->createMock(OutputStyle::class);
        $this->migrator = resolve(Migrator::class)->setOutput($this->output);

        // create fixtures
        DB::table($this->table)->insert([
            ['migration' => '2018_12_01_081000_create_test_index', 'batch' => 1],
        ]);
    }

    public function test_single_migration_can_not_be_executed_if_file_does_not_exist(): void
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Migration is not found:</error> 3020_11_01_045023_drop_test_index');

        $this->assertSame(
            $this->migrator,
            $this->migrator->migrateOne('3020_11_01_045023_drop_test_index')
        );
    }

    public function test_single_migration_can_be_executed_if_file_exists(): void
    {
        Index::shouldReceive('putMapping')->once();

        $this->output
            ->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                ['<comment>Migrating:</comment> 2019_08_10_142230_update_test_index_mapping'],
                ['<info>Migrated:</info> 2019_08_10_142230_update_test_index_mapping']
            );

        $this->assertSame(
            $this->migrator,
            $this->migrator->migrateOne('2019_08_10_142230_update_test_index_mapping')
        );

        $this->assertDatabaseHas($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 2,
        ]);
    }

    public function test_all_migrations_can_not_be_executed_if_directory_is_empty(): void
    {
        // create a temporary empty directory and reconfigure the package to use it
        $tmpDirectory = $this->config->get('elastic.migrations.storage.default_path') . '/tmp';
        @mkdir($tmpDirectory);
        $this->config->set('elastic.migrations.storage.default_path', $tmpDirectory);

        // create a new instance to apply the new config
        $this->app->forgetInstance(MigrationStorage::class);
        $migrator = resolve(Migrator::class)->setOutput($this->output);

        // check that there is nothing to migrate
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('<info>Nothing to migrate</info>');

        $this->assertSame($migrator, $migrator->migrateAll());

        // remove the temporary directory
        @rmdir($tmpDirectory);
    }

    public function test_all_migrations_can_be_executed_if_directory_is_not_empty(): void
    {
        Index::shouldReceive('putMapping')->once();

        $this->output
            ->expects($this->exactly(4))
            ->method('writeln')
            ->withConsecutive(
                ['<comment>Migrating:</comment> 2019_08_10_142230_update_test_index_mapping'],
                ['<info>Migrated:</info> 2019_08_10_142230_update_test_index_mapping'],
                ['<comment>Migrating:</comment> 2024_04_08_113433_test_new_format'],
                ['<info>Migrated:</info> 2024_04_08_113433_test_new_format'],
            );

        $this->assertSame(
            $this->migrator,
            $this->migrator->migrateAll()
        );

        $this->assertDatabaseHas($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 2,
        ]);
    }

    public function test_single_migration_can_not_be_rolled_back_if_file_does_not_exist(): void
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Migration is not found:</error> 3020_11_01_045023_drop_test_index');

        $this->assertSame(
            $this->migrator,
            $this->migrator->rollbackOne('3020_11_01_045023_drop_test_index')
        );
    }

    public function test_single_migration_can_not_be_rolled_back_if_file_is_not_yet_migrated(): void
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Migration is not yet migrated:</error> 2019_08_10_142230_update_test_index_mapping');

        $this->assertSame(
            $this->migrator,
            $this->migrator->rollbackOne('2019_08_10_142230_update_test_index_mapping')
        );
    }

    public function test_single_migration_can_be_rolled_back_if_file_exists_and_is_migrated(): void
    {
        Index::shouldReceive('drop')->once();

        $this->output
            ->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                ['<comment>Rolling back:</comment> 2018_12_01_081000_create_test_index'],
                ['<info>Rolled back:</info> 2018_12_01_081000_create_test_index']
            );

        $this->assertSame(
            $this->migrator,
            $this->migrator->rollbackOne('2018_12_01_081000_create_test_index')
        );

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2018_12_01_081000_create_test_index',
            'batch' => 1,
        ]);
    }

    public function test_last_batch_can_not_be_rolled_back_if_some_files_are_missing(): void
    {
        // imitate, that migration has already been migrated
        DB::table($this->table)->insert([
            ['migration' => '2019_03_10_101500_create_test_index', 'batch' => 2],
        ]);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Migration is not found:</error> 2019_03_10_101500_create_test_index');

        $this->assertSame(
            $this->migrator,
            $this->migrator->rollbackLastBatch()
        );
    }

    public function test_last_batch_can_be_rolled_back_if_all_files_are_present(): void
    {
        // imitate, that migration has already been migrated
        DB::table($this->table)->insert([
            ['migration' => '2019_08_10_142230_update_test_index_mapping', 'batch' => 4],
        ]);

        Index::shouldReceive('putMapping')->once();

        $this->output
            ->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                ['<comment>Rolling back:</comment> 2019_08_10_142230_update_test_index_mapping'],
                ['<info>Rolled back:</info> 2019_08_10_142230_update_test_index_mapping']
            );

        $this->assertSame(
            $this->migrator,
            $this->migrator->rollbackLastBatch()
        );

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 4,
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
            ->expects($this->once())
            ->method('writeln')
            ->with(
                '<error>Migration is not found:</error> 2019_03_10_101500_create_test_index,2019_01_01_053550_drop_test_index'
            );

        $this->assertSame(
            $this->migrator,
            $this->migrator->rollbackAll()
        );
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
            ->expects($this->exactly(4))
            ->method('writeln')
            ->withConsecutive(
                ['<comment>Rolling back:</comment> 2019_08_10_142230_update_test_index_mapping'],
                ['<info>Rolled back:</info> 2019_08_10_142230_update_test_index_mapping'],
                ['<comment>Rolling back:</comment> 2018_12_01_081000_create_test_index'],
                ['<info>Rolled back:</info> 2018_12_01_081000_create_test_index']
            );

        $this->assertSame(
            $this->migrator,
            $this->migrator->rollbackAll()
        );

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2019_08_10_142230_update_test_index_mapping',
            'batch' => 2,
        ]);

        $this->assertDatabaseMissing($this->table, [
            'migration' => '2018_12_01_081000_create_test_index',
            'batch' => 1,
        ]);
    }

    public function statusDataProvider(): array
    {
        return [
            'all migrations' => [
                'onlyPending' => false,
                'expectedOutput' => [
                    ['2018_12_01_081000_create_test_index', '<fg=green;options=bold>Ran</> <fg=gray>(last batch)</>'],
                    ['2019_08_10_142230_update_test_index_mapping', '<fg=yellow;options=bold>Pending</>'],
                    ['2024_04_08_113433_test_new_format', '<fg=yellow;options=bold>Pending</>'],
                ],
            ],
            'pending migrations' => [
                'onlyPending' => true,
                'expectedOutput' => [
                    ['2019_08_10_142230_update_test_index_mapping', '<fg=yellow;options=bold>Pending</>'],
                    ['2024_04_08_113433_test_new_format', '<fg=yellow;options=bold>Pending</>'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider statusDataProvider
     */
    public function test_status_is_displayed_correctly(bool $onlyPending, array $expectedOutput): void
    {
        $this->output
            ->expects($this->once())
            ->method('table')
            ->with(['<fg=gray>Migration name</>', '<fg=gray>Status</>'], $expectedOutput);

        $this->assertSame(
            $this->migrator,
            $this->migrator->showStatus($onlyPending)
        );
    }

    public function test_migrator_is_ready_when_repository_and_storage_are_ready(): void
    {
        $this->assertTrue($this->migrator->isReady());
    }

    public function test_migrator_is_not_ready_when_repository_is_not_ready(): void
    {
        Schema::drop($this->table);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Migration table is not yet created</error>');

        $this->assertFalse($this->migrator->isReady());
    }

    public function test_migrator_is_not_ready_when_storage_is_not_ready(): void
    {
        $this->config->set('elastic.migrations.storage.default_path', '/non_existing_directory');

        // create a new instance to apply the new config
        $this->app->forgetInstance(MigrationStorage::class);
        $migrator = $this->app->make(Migrator::class)->setOutput($this->output);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Default migration path is not yet created</error>');

        $this->assertFalse($migrator->isReady());
    }
}
