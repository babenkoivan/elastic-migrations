<?php declare(strict_types=1);

namespace ElasticMigrations\Repositories;

use ElasticMigrations\ReadinessInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

final class MigrationRepository implements ReadinessInterface
{
    /**
     * @var string
     */
    private $table;
    /**
     * @var string
     */
    private $connection;

    public function __construct()
    {
        $this->table = config('elastic.migrations.table');
        $this->connection = config('elastic.migrations.connection');
    }

    public function insert(string $fileName, int $batch): bool
    {
        return $this->table()->insert([
            'migration' => $fileName,
            'batch' => $batch,
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
        /** @var stdClass|null $record */
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
            ->orderBy('migration', 'desc')
            ->pluck('migration');
    }

    public function getAll(): Collection
    {
        return $this->table()
            ->orderBy('migration', 'desc')
            ->pluck('migration');
    }

    private function table(): Builder
    {
        return DB::connection($this->connection)->table($this->table);
    }

    public function isReady(): bool
    {
        return Schema::connection($this->connection)->hasTable($this->table);
    }
}
