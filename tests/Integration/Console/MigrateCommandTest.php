<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Tests\Integration\Console;

use OpenSearch\Migrations\Console\MigrateCommand;
use OpenSearch\Migrations\Migrator;
use OpenSearch\Migrations\Tests\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \OpenSearch\Migrations\Console\MigrateCommand
 */
final class MigrateCommandTest extends TestCase
{
    private MockObject $migrator;
    private MigrateCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);
        $this->app->instance(Migrator::class, $this->migrator);

        $this->command = new MigrateCommand();
        $this->command->setLaravel($this->app);
    }

    public function test_does_nothing_if_migrator_is_not_ready(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(false);

        $this->migrator
            ->expects($this->never())
            ->method('migrateOne');

        $this->migrator
            ->expects($this->never())
            ->method('migrateAll');

        $result = $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );

        $this->assertSame(1, $result);
    }

    public function test_runs_one_migration_if_file_name_is_provided(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(true);

        $this->migrator
            ->expects($this->once())
            ->method('migrateOne')
            ->with('test_file_name');

        $result = $this->command->run(
            new ArrayInput(['--force' => true, 'name' => 'test_file_name']),
            new NullOutput()
        );

        $this->assertSame(0, $result);
    }

    public function test_runs_all_migrations_if_file_name_is_not_provided(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(true);

        $this->migrator
            ->expects($this->once())
            ->method('migrateAll');

        $result = $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );

        $this->assertSame(0, $result);
    }
}
