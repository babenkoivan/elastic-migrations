<?php
declare(strict_types=1);

namespace ElasticMigrations\Repositories;

use ElasticMigrations\Contracts\ReadinessInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MigrationRepository implements ReadinessInterface
{
    /**
     * @var string
     */
    private $table;

    public function __construct()
    {
        $this->table = config('elastic.migrations.table');
    }

    public function insert(string $fileName, int $batch): bool
    {
        return $this->table()->insert([
            'migration' => $fileName,
            'batch' => $batch
        ]);
    }

    public function exists(string $fileName): bool
    {
        return $this->table()
            ->where('migration', $fileName)
            ->exists();
    }

    public function delete(string $fileName): bool
    {
        return (bool)$this->table()
            ->where('migration', $fileName)
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

    public function isReady(): bool
    {
        return Schema::hasTable($this->table);
    }
}
