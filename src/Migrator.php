<?php declare(strict_types=1);

namespace Elastic\Migrations;

use Elastic\Migrations\Factories\MigrationFactory;
use Elastic\Migrations\Filesystem\MigrationFile;
use Elastic\Migrations\Filesystem\MigrationStorage;
use Elastic\Migrations\Repositories\MigrationRepository;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

class Migrator implements ReadinessInterface
{
    private OutputStyle $output;
    private MigrationRepository $migrationRepository;
    private MigrationStorage $migrationStorage;
    private MigrationFactory $migrationFactory;

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
        $file = $this->migrationStorage->whereName($fileName);

        if (is_null($file)) {
            $this->output->writeln('<error>Migration is not found:</error> ' . $fileName);
        } else {
            $this->migrate(collect([$file]));
        }

        return $this;
    }

    public function migrateAll(): self
    {
        $files = $this->migrationStorage->all();
        $migratedFileNames = $this->migrationRepository->all();

        $nonMigratedFiles = $files->filter(
            static fn (MigrationFile $file) => !$migratedFileNames->contains($file->name())
        );

        $this->migrate($nonMigratedFiles);

        return $this;
    }

    public function rollbackOne(string $fileName): self
    {
        $file = $this->migrationStorage->whereName($fileName);

        if (is_null($file)) {
            $this->output->writeln('<error>Migration is not found:</error> ' . $fileName);
        } elseif (!$this->migrationRepository->exists($file->name())) {
            $this->output->writeln('<error>Migration is not yet migrated:</error> ' . $file->name());
        } else {
            $this->rollback(collect([$file->name()]));
        }

        return $this;
    }

    public function rollbackLastBatch(): self
    {
        $fileNames = $this->migrationRepository->lastBatch();

        $this->rollback($fileNames);

        return $this;
    }

    public function rollbackAll(): self
    {
        $fileNames = $this->migrationRepository->all();

        $this->rollback($fileNames);

        return $this;
    }

    public function showStatus(bool $onlyPending = false): self
    {
        $files = $this->migrationStorage->all();
        $migratedFiles = $this->migrationRepository->all();
        $lastBatch = $this->migrationRepository->lastBatch();

        $rows = $files->map(
            static fn (MigrationFile $file) => [
                $file->name(),
                $migratedFiles->contains($file->name())
                    ? '<fg=green;options=bold>Ran</>' . ($lastBatch->contains($file->name()) ? ' <fg=gray>(last batch)</>' : '')
                    : '<fg=yellow;options=bold>Pending</>',
            ]
        )->when($onlyPending, static fn (Collection $rows) => $rows->filter(
            static fn (array $row) => strpos($row[1], 'Pending') !== false
        )->values())->toArray();

        if ($rows !== []) {
            $headers = ['<fg=gray>Migration name</>', '<fg=gray>Status</>'];
            $this->output->table($headers, $rows);
            return $this;
        }

        $this->output->writeln(
            sprintf('<fg=yellow;options=bold>%s</>', $onlyPending ? 'No pending migrations' : 'No migrations found')
        );

        return $this;
    }

    private function migrate(Collection $files): self
    {
        if ($files->isEmpty()) {
            $this->output->writeln('<info>Nothing to migrate</info>');
            return $this;
        }

        $nextBatchNumber = $this->migrationRepository->lastBatchNumber() + 1;

        $files->each(function (MigrationFile $file) use ($nextBatchNumber) {
            $this->output->writeln('<comment>Migrating:</comment> ' . $file->name());

            $migration = $this->migrationFactory->makeFromFile($file);
            $migration->up();

            $this->migrationRepository->insert($file->name(), $nextBatchNumber);

            $this->output->writeln('<info>Migrated:</info> ' . $file->name());
        });

        return $this;
    }

    private function rollback(Collection $fileNames): self
    {
        $files = $fileNames->map(
            fn (string $fileName) => $this->migrationStorage->whereName($fileName)
        )->filter();

        if ($fileNames->isEmpty()) {
            $this->output->writeln('<info>Nothing to roll back</info>');
            return $this;
        }

        if ($fileNames->count() !== $files->count()) {
            $this->output->writeln(
                '<error>Migration is not found:</error> ' .
                implode(
                    ',',
                    $fileNames->diff($files->map(static fn (MigrationFile $file) => $file->name()))->toArray()
                )
            );

            return $this;
        }

        $files->each(function (MigrationFile $file) {
            $this->output->writeln('<comment>Rolling back:</comment> ' . $file->name());

            $migration = $this->migrationFactory->makeFromFile($file);
            $migration->down();

            $this->migrationRepository->delete($file->name());

            $this->output->writeln('<info>Rolled back:</info> ' . $file->name());
        });

        return $this;
    }

    public function isReady(): bool
    {
        if (!$isMigrationRepositoryReady = $this->migrationRepository->isReady()) {
            $this->output->writeln('<error>Migration table is not yet created</error>');
        }

        if (!$isMigrationStorageReady = $this->migrationStorage->isReady()) {
            $this->output->writeln('<error>Default migration path is not yet created</error>');
        }

        return $isMigrationRepositoryReady && $isMigrationStorageReady;
    }
}
