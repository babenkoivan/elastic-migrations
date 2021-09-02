<?php declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Console;

use ElasticMigrations\Console\RefreshCommand;
use ElasticMigrations\Migrator;
use ElasticMigrations\Tests\Integration\TestCase;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \ElasticMigrations\Console\RefreshCommand
 */
final class RefreshCommandTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $migrator;
    /**
     * @var RefreshCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);

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

        $output = $this->app->make(OutputStyle::class, [
            'input' => new ArrayInput(['--force' => true]),
            'output' =>  new NullOutput(),
        ]);

        $this->command->setOutput($output);

        $result = $this->command->handle($this->migrator);

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

        $output = $this->app->make(OutputStyle::class, [
            'input' => new ArrayInput(['--force' => true]),
            'output' =>   new NullOutput(),
        ]);

        $this->command->setOutput($output);

        $result = $this->command->handle($this->migrator);

        $this->assertSame(0, $result);
    }
}
