<?php declare(strict_types=1);

namespace ElasticMigrations;

use ElasticMigrations\Factories\MigrationFactory;
use ElasticMigrations\Filesystem\MigrationFile;
use ElasticMigrations\Filesystem\MigrationStorage;
use ElasticMigrations\Repositories\MigrationRepository;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

class Migrator implements ReadinessInterface
{
    /**
     * @var OutputStyle
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

    public function setOutput(OutputStyle $output): self
    {
        $this->output = $output;
        return $this;
    }

    public function migrateOne(string $fileName): self
    {
        $file = $this->migrationStorage->findByName($fileName);

        if (is_null($file)) {
            $this->output->writeln('<error>Migration is not found:</error> ' . $fileName);
        } else {
            $this->migrate(collect([$file]));
        }

        return $this;
    }

    public function migrateAll(): self
    {
        $files = $this->migrationStorage->findAll();
        $migratedFileNames = $this->migrationRepository->getAll();

        $nonMigratedFiles = $files->filter(static function (MigrationFile $file) use ($migratedFileNames) {
            return !$migratedFileNames->contains($file->getName());
        });

        $this->migrate($nonMigratedFiles);

        return $this;
    }

    public function rollbackOne(string $fileName): self
    {
        $file = $this->migrationStorage->findByName($fileName);

        if (is_null($file)) {
            $this->output->writeln('<error>Migration is not found:</error> ' . $fileName);
        } elseif (!$this->migrationRepository->exists($file->getName())) {
            $this->output->writeln('<error>Migration is not yet migrated:</error> ' . $file->getName());
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

    public function reset(): self
    {
        $this->migrationRepository->clear();

        return $this;
    }

    public function showStatus(): self
    {
        $files = $this->migrationStorage->findAll();

        $migratedFileNames = $this->migrationRepository->getAll();
        $migratedLastBatchFileNames = $this->migrationRepository->getLastBatch();

        $headers = ['Ran?', 'Last batch?', 'Migration'];

        $rows = $files->map(static function (MigrationFile $file) use ($migratedFileNames, $migratedLastBatchFileNames) {
            return [
                $migratedFileNames->contains($file->getName()) ? '<info>Yes</info>' : '<comment>No</comment>',
                $migratedLastBatchFileNames->contains($file->getName()) ? '<info>Yes</info>' : '<comment>No</comment>',
                $file->getName(),
            ];
        })->toArray();

        $this->output->table($headers, $rows);

        return $this;
    }

    private function migrate(Collection $files): self
    {
        if ($files->isEmpty()) {
            $this->output->writeln('<info>Nothing to migrate</info>');
            return $this;
        }

        $nextBatchNumber = $this->migrationRepository->getLastBatchNumber() + 1;

        $files->each(function (MigrationFile $file) use ($nextBatchNumber) {
            $this->output->writeln('<comment>Migrating:</comment> ' . $file->getName());

            $migration = $this->migrationFactory->makeByFile($file);
            $migration->up();

            $this->migrationRepository->insert($file->getName(), $nextBatchNumber);

            $this->output->writeln('<info>Migrated:</info> ' . $file->getName());
        });

        return $this;
    }

    private function rollback(Collection $fileNames): self
    {
        $files = $fileNames->map(function (string $fileName) {
            return $this->migrationStorage->findByName($fileName);
        })->filter();

        if ($fileNames->isEmpty()) {
            $this->output->writeln('<info>Nothing to roll back</info>');
            return $this;
        } elseif ($fileNames->count() != $files->count()) {
            $this->output->writeln(
                '<error>Migration is not found:</error> ' .
                implode(',', $fileNames->diff($files->map(static function (MigrationFile $file) {
                    return $file->getName();
                }))->toArray())
            );

            return $this;
        }

        $files->each(function (MigrationFile $file) {
            $this->output->writeln('<comment>Rolling back:</comment> ' . $file->getName());

            $migration = $this->migrationFactory->makeByFile($file);
            $migration->down();

            $this->migrationRepository->delete($file->getName());

            $this->output->writeln('<info>Rolled back:</info> ' . $file->getName());
        });

        return $this;
    }

    public function isReady(): bool
    {
        if (!$isMigrationRepositoryReady = $this->migrationRepository->isReady()) {
            $this->output->writeln('<error>Migration table is not yet created</error>');
        }

        if (!$isMigrationStorageReady = $this->migrationStorage->isReady()) {
            $this->output->writeln('<error>Migration directory is not yet created</error>');
        }

        return $isMigrationRepositoryReady && $isMigrationStorageReady;
    }
}
