<?php declare(strict_types=1);

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

        /** @var string $migrationStub */
        $migrationStub = file_get_contents(dirname(__DIR__, 3) . '/src/Console/stubs/migration.blank.stub');

        $migrationStorageMock
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->stringEndsWith('_test_migration_creation'),
                str_replace('DummyClass', 'TestMigrationCreation', $migrationStub)
            );

        $command = new MakeCommand($fileSystem, $migrationStorageMock);
        $command->setLaravel($this->app);

        $input = new ArrayInput(['name' => 'test_migration_creation']);
        $output = new BufferedOutput();

        $resultCode = $command->run($input, $output);
        $resultMessage = $output->fetch();

        $this->assertSame(0, $resultCode);

        $this->assertStringContainsString('Created migration', $resultMessage);
        $this->assertStringContainsString('_test_migration_creation', $resultMessage);
    }
}
