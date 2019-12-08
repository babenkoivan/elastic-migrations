<?php
declare(strict_types=1);

namespace ElasticMigrations\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class MigrationRepository
{
    /**
     * @var string
     */
    private $table;

    public function __construct()
    {
        $this->table = config('elastic.migrations.table');
    }

    public function insert(string $migration, int $batch): bool
    {
        $record = compact('migration', 'batch');
        return $this->table()->insert($record);
    }

    public function exists(string $migration): bool
    {
        return $this->table()
            ->where('migration', $migration)
            ->exists();
    }

    public function delete(string $migration): bool
    {
        return (bool)$this->table()
            ->where('migration', $migration)
            ->delete();
    }

    public function getLastBatchNumber(): ?int
    {
        $record = $this->table()
            ->select('batch')
            ->orderBy('batch', 'desc')
            ->first();

        return isset($record) ? (int)$record->batch : null;
    }

    public function getLastBatch(): Collection
    {
        return $this->table()
            ->where('batch', $this->getLastBatchNumber())
            ->orderBy('migration', 'asc')
            ->pluck('migration');
    }

    public function getAll(): Collection
    {
        return $this->table()
            ->orderBy('migration', 'asc')
            ->pluck('migration');
    }

    private function table(): Builder
    {
        return DB::table($this->table);
    }
}
