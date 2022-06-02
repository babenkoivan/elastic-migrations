<?php declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Console;

use ElasticMigrations\Console\StatusCommand;
use ElasticMigrations\Migrator;
use ElasticMigrations\Tests\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \ElasticMigrations\Console\StatusCommand
 */
final class StatusCommandTest extends TestCase
{
    private MockObject $migrator;
    private StatusCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);
        $this->app->instance(Migrator::class, $this->migrator);

        $this->command = new StatusCommand();
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
            ->method('showStatus');

        $result = $this->command->run(
            new ArrayInput([]),
            new NullOutput()
        );

        $this->assertSame(1, $result);
    }

    public function test_displays_each_migration_status_if_migrator_is_ready(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(true);

        $this->migrator
            ->expects($this->once())
            ->method('showStatus');

        $result = $this->command->run(
            new ArrayInput([]),
            new NullOutput()
        );

        $this->assertSame(0, $result);
    }
}
