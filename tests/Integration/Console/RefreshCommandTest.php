<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Console;

use ElasticMigrations\Console\RefreshCommand;
use ElasticMigrations\Migrator;
use ElasticMigrations\Tests\Integration\TestCase;
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
     * @var MigrateCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);

        $this->command = new RefreshCommand($this->migrator);
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

        $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );
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

        $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );
    }
}
