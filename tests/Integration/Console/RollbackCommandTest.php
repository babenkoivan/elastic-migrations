<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration\Console;

use Elastic\Migrations\Console\RollbackCommand;
use Elastic\Migrations\Migrator;
use Elastic\Migrations\Tests\Integration\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(RollbackCommand::class)]
final class RollbackCommandTest extends TestCase
{
    private MockObject $migrator;
    private RollbackCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);
        $this->app->instance(Migrator::class, $this->migrator);

        $this->command = new RollbackCommand();
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
            ->method('rollbackOne');

        $this->migrator
            ->expects($this->never())
            ->method('rollbackLastBatch');

        $result = $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );

        $this->assertSame(1, $result);
    }

    public function test_rollbacks_one_migration_if_file_name_is_provided(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(true);

        $this->migrator
            ->expects($this->once())
            ->method('rollbackOne')
            ->with('test_file_name');

        $result = $this->command->run(
            new ArrayInput(['--force' => true, 'name' => 'test_file_name']),
            new NullOutput()
        );

        $this->assertSame(0, $result);
    }

    public function test_rollbacks_last_batch_if_file_name_is_not_provided(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(true);

        $this->migrator
            ->expects($this->once())
            ->method('rollbackLastBatch');

        $result = $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );

        $this->assertSame(0, $result);
    }
}
