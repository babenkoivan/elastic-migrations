<?php
declare(strict_types=1);

namespace ElasticMigrations;

use ElasticMigrations\Contracts\ReadinessInterface;
use ElasticMigrations\Factories\MigrationFactory;
use ElasticMigrations\Filesystem\MigrationFile;
use ElasticMigrations\Filesystem\MigrationStorage;
use ElasticMigrations\Repositories\MigrationRepository;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Style\StyleInterface;

final class Migrator implements ReadinessInterface
{
    /**
     * @var StyleInterface
     */
    private $output;
    /**
     * @var MigrationRepository
     */
    private $migrationRepository;
    /**
     * @var MigrationStorage
     */
    private $migrationStorage;
    /**
     * @var MigrationFactory
     */
    private $migrationFactory;

    public function __construct(
        MigrationRepository $migrationRepository,
        MigrationStorage $migrationStorage,
        MigrationFactory $migrationFactory
    ) {
        $this->migrationStorage = $migrationStorage;
        $this->migrationRepository = $migrationRepository;
        $this->migrationFactory = $migrationFactory;
    }

    public function setOutput(StyleInterface $output): self
    {
        $this->output = $output;
        return $this;
    }

    public function migrateOne(string $fileName = null): self
    {
        $file = $this->migrationStorage->findByName($fileName);

        if (is_null($file)) {
            $this->output->error('Migration is not found: ' . $fileName);
        } else {
            $this->migrate(collect([$file]));
        }

        return $this;
    }

    public function migrateAll(): self
    {
        $files = $this->migrationStorage->findAll();
        $migratedNames = $this->migrationRepository->getAll();

        $nonMigratedFiles = $files->filter(function (MigrationFile $file) use ($migratedNames) {
            return !$migratedNames->contains($file->getName());
        });

        $this->migrate($nonMigratedFiles);

        return $this;
    }

    public function rollbackOne(string $fileName = null): self
    {
        $file = $this->migrationStorage->findByName($fileName);

        if (is_null($file)) {
            $this->output->error('Migration is not found: ' . $fileName);
        } elseif (!$this->migrationRepository->exists($file->getName())) {
            $this->output->error('Migration is not yet migrated: ' . $file->getName());
        } else {
            $this->rollback(collect([$file->getName()]));
        }

        return $this;
    }

    public function rollbackLastBatch(): self
    {
        $fileNames = $this->migrationRepository->getLastBatch();

        $this->rollback($fileNames);

        return $this;
    }

    public function rollbackAll(): self
    {
        $fileNames = $this->migrationRepository->getAll();

        $this->rollback($fileNames);

        return $this;
    }

    public function showStatus(): self
    {
        $files = $this->migrationStorage->findAll();

        $migratedNames = $this->migrationRepository->getAll();
        $migratedLastBatchNames = $this->migrationRepository->getLastBatch();

        $headers = ['Ran?', 'Last batch?', 'Migration'];

        $rows = $files->map(function (MigrationFile $file) use ($migratedNames, $migratedLastBatchNames) {
            return [
                $migratedNames->contains($file->getName()) ? 'Yes' : 'No',
                $migratedLastBatchNames->contains($file->getName()) ? 'Yes' : 'No',
                $file->getName(),
            ];
        })->toArray();

        $this->output->table($headers, $rows);

        return $this;
    }

    private function migrate(Collection $files): self
    {
        if ($files->isEmpty()) {
            $this->output->warning('Nothing to migrate');
            return $this;
        }

        $nextBatchNumber = $this->migrationRepository->getLastBatchNumber() + 1;

        $files->each(function (MigrationFile $file) use ($nextBatchNumber) {
            $this->output->note('Migrating: ' . $file->getName());

            $migration = $this->migrationFactory->makeByFile($file);
            $migration->up();

            $this->migrationRepository->insert($file->getName(), $nextBatchNumber);

            $this->output->success('Migrated: ' . $file->getName());
        });

        return $this;
    }

    private function rollback(Collection $fileNames): self
    {
        $files = $fileNames->map(function (string $fileName) {
            return $this->migrationStorage->findByName($fileName);
        })->filter();

        if ($fileNames->isEmpty()) {
            $this->output->warning('Nothing to roll back');
            return $this;
        } elseif ($fileNames->count() != $files->count()) {
            $this->output->error(
                'Migration is not found: ' . implode(',', $fileNames->diff($files->map(function (MigrationFile $file) {
                    return $file->getName();
                }))->toArray())
            );

            return $this;
        }

        $files->each(function (MigrationFile $file) {
            $this->output->note(sprintf('Rolling back: %s', $file->getName()));

            $migration = $this->migrationFactory->makeByFile($file);
            $migration->down();

            $this->migrationRepository->delete($file->getName());

            $this->output->success(sprintf('Rolled back: %s', $file->getName()));
        });

        return $this;
    }

    public function isReady(): bool
    {
        if (!$isMigrationRepositoryReady = $this->migrationRepository->isReady()) {
            $this->output->error('Migration table is not yet created');
        }

        if (!$isMigrationStorageReady = $this->migrationStorage->isReady()) {
            $this->output->error('Migration directory is not yet created');
        }

        return $isMigrationRepositoryReady && $isMigrationStorageReady;
    }
}
