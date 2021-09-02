<?php declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Console;

use ElasticMigrations\Console\StatusCommand;
use ElasticMigrations\Migrator;
use ElasticMigrations\Tests\Integration\TestCase;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \ElasticMigrations\Console\StatusCommand
 */
final class StatusCommandTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $migrator;
    /**
     * @var StatusCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);

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

        $output = $this->app->make(
            OutputStyle::class,
            [
                'input' => new ArrayInput([]),
                'output' => new NullOutput(),
            ]
        );

        $this->command->setOutput($output);

        $result = $this->command->handle($this->migrator);

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

        $output = $this->app->make(
            OutputStyle::class,
            [
                'input' => new ArrayInput([]),
                'output' => new NullOutput(),
            ]
        );

        $this->command->setOutput($output);

        $result = $this->command->handle($this->migrator);

        $this->assertSame(0, $result);
    }
}
