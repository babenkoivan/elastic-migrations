<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration\Console;

use Elastic\Migrations\Console\RefreshCommand;
use Elastic\Migrations\Migrator;
use Elastic\Migrations\Tests\Integration\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(RefreshCommand::class)]
final class RefreshCommandTest extends TestCase
{
    private MockObject $migrator;
    private RefreshCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);
        $this->app->instance(Migrator::class, $this->migrator);

        $this->command = new RefreshCommand();
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
            ->method('rollbackAll');

        $this->migrator
            ->expects($this->never())
            ->method('migrateAll');

        $result = $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );

        $this->assertSame(1, $result);
    }

    public function test_resets_and_reruns_all_migrations_if_migrator_is_ready(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(true);

        $this->migrator
            ->expects($this->once())
            ->method('rollbackAll');

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
