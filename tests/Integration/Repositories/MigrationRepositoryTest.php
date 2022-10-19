<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenSearch\Migrations\Repositories\MigrationRepository;
use OpenSearch\Migrations\Tests\Integration\TestCase;

/**
 * @covers \OpenSearch\Migrations\Repositories\MigrationRepository
 */
final class MigrationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private string $table;
    private MigrationRepository $migrationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = $this->config->get('opensearch.migrations.database.table');

        // create fixtures
        DB::table($this->table)->insert([
            ['migration' => '2019_08_10_142230_update_test_index_mapping', 'batch' => 2],
            ['migration' => '2018_12_01_081000_create_test_index', 'batch' => 1],
        ]);

        $this->migrationRepository = new MigrationRepository();
    }

    public function test_record_can_be_inserted(): void
    {
        $this->migrationRepository->insert('2019_12_12_201657_update_test_index_settings', 3);

        $this->assertDatabaseHas(
            $this->table,
            ['migration' => '2019_12_12_201657_update_test_index_settings', 'batch' => 3]
        );
    }

    public function test_record_passes_existence_check(): void
    {
        $this->assertTrue($this->migrationRepository->exists('2018_12_01_081000_create_test_index'));
        $this->assertFalse($this->migrationRepository->exists('2019_12_05_092345_drop_test_index'));
    }

    public function test_record_can_be_deleted(): void
    {
        $this->migrationRepository->delete('2019_12_01_081000_create_test_index');

        $this->assertDatabaseMissing(
            $this->table,
            ['migration' => '2019_12_01_081000_create_test_index', 'batch' => 1]
        );
    }

    public function test_all_records_can_be_retrieved(): void
    {
        $this->assertSame(
            $this->migrationRepository->all()->toArray(),
            [
                '2019_08_10_142230_update_test_index_mapping',
                '2018_12_01_081000_create_test_index',
            ]
        );
    }

    public function test_last_batch_number_can_be_retrieved(): void
    {
        $this->assertSame(2, $this->migrationRepository->lastBatchNumber());

        DB::table($this->table)->delete();
        $this->assertNull($this->migrationRepository->lastBatchNumber());
    }

    public function test_last_record_batch_can_be_retrieved(): void
    {
        $this->assertSame(
            $this->migrationRepository->lastBatch()->toArray(),
            [
                '2019_08_10_142230_update_test_index_mapping',
            ]
        );
    }

    public function test_repository_is_ready_when_table_exists(): void
    {
        $this->assertTrue($this->migrationRepository->isReady());
    }

    public function test_repository_is_not_ready_when_table_does_not_exist(): void
    {
        Schema::drop($this->table);

        $this->assertFalse($this->migrationRepository->isReady());
    }

    public function test_repository_can_delete_all_records(): void
    {
        $this->assertCount(2, $this->migrationRepository->all());

        $this->migrationRepository->purge();

        $this->assertCount(0, $this->migrationRepository->all());
    }
}
