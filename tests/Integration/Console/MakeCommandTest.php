<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Console;

use ElasticMigrations\Console\MakeCommand;
use ElasticMigrations\Filesystem\MigrationStorage;
use ElasticMigrations\Tests\Integration\TestCase;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \ElasticMigrations\Console\MakeCommand
 */
final class MakeCommandTest extends TestCase
{
    public function test_migration_file_can_be_created(): void
    {
        $fileSystem = resolve(Filesystem::class);
        $migrationStorageMock = $this->createMock(MigrationStorage::class);

        $migrationStorageMock
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->stringEndsWith('_test_migration_creation'),
                str_replace(
                    'DummyClass',
                    'TestMigrationCreation',
                    file_get_contents(realpath(__DIR__ . '/../../../src/Console/stubs/migration.blank.stub'))
                )
            );

        $command = new MakeCommand($fileSystem, $migrationStorageMock);
        $command->setLaravel($this->app);

        $input = new ArrayInput(['name' => 'test_migration_creation']);
        $output = new BufferedOutput();

        $command->run($input, $output);

        $this->assertRegExp('/^Created migration: .+?_test_migration_creation$/', $output->fetch());
    }
}
