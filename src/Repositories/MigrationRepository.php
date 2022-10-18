<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenSearch\Migrations\ReadinessInterface;
use stdClass;

class MigrationRepository implements ReadinessInterface
{
    private string $table;
    private ?string $connection;

    public function __construct()
    {
        $this->table = config('opensearch.migrations.database.table');
        $this->connection = config('opensearch.migrations.database.connection');
    }

    public function insert(string $fileName, int $batchNumber): bool
    {
        return $this->table()->insert([
            'migration' => $fileName,
            'batch' => $batchNumber,
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

    public function purge(): void
    {
        $this->table()->delete();
    }

    public function lastBatchNumber(): ?int
    {
        /** @var stdClass|null $record */
        $record = $this->table()
            ->select('batch')
            ->orderBy('batch', 'desc')
            ->first();

        return isset($record) ? (int)$record->batch : null;
    }

    public function lastBatch(): Collection
    {
        return $this->table()
            ->where('batch', $this->lastBatchNumber())
            ->orderBy('migration', 'desc')
            ->pluck('migration');
    }

    public function all(): Collection
    {
        return $this->table()
            ->orderBy('migration', 'desc')
            ->pluck('migration');
    }

    public function isReady(): bool
    {
        return Schema::connection($this->connection)->hasTable($this->table);
    }

    private function table(): Builder
    {
        return DB::connection($this->connection)->table($this->table);
    }
}
