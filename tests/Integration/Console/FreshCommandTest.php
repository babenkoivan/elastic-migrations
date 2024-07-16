<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration\Console;

use Elastic\Migrations\Console\FreshCommand;
use Elastic\Migrations\IndexManagerInterface;
use Elastic\Migrations\Migrator;
use Elastic\Migrations\Repositories\MigrationRepository;
use Elastic\Migrations\Tests\Integration\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(FreshCommand::class)]
final class FreshCommandTest extends TestCase
{
    private MockObject $migrator;
    private MockObject $migrationRepository;
    private MockObject $indexManager;
    private FreshCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrator = $this->createMock(Migrator::class);
        $this->app->instance(Migrator::class, $this->migrator);

        $this->migrationRepository = $this->createMock(MigrationRepository::class);
        $this->app->instance(MigrationRepository::class, $this->migrationRepository);

        $this->indexManager = $this->createMock(IndexManagerInterface::class);
        $this->app->instance(IndexManagerInterface::class, $this->indexManager);

        $this->command = new FreshCommand();
        $this->command->setLaravel($this->app);
    }

    public function test_does_nothing_if_migrator_is_not_ready(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(false);

        $this->indexManager
            ->expects($this->never())
            ->method('drop');

        $this->migrationRepository
            ->expects($this->never())
            ->method('purge');

        $this->migrator
            ->expects($this->never())
            ->method('migrateAll');

        $result = $this->command->run(
            new ArrayInput(['--force' => true]),
            new NullOutput()
        );

        $this->assertSame(1, $result);
    }

    public function test_drops_indices_and_migration(): void
    {
        $this->migrator
            ->expects($this->once())
            ->method('isReady')
            ->willReturn(true);

        $this->indexManager
            ->expects($this->once())
            ->method('drop')
            ->with('*');

        $this->migrationRepository
            ->expects($this->once())
            ->method('purge');

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
