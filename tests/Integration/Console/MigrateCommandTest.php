<?php declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Console;

use ElasticMigrations\Console\MigrateCommand;
use ElasticMigrations\Migrator;
use ElasticMigrations\Tests\Integration\TestCase;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \ElasticMigrations\Console\MigrateCommand
 */
final class MigrateCommandTest extends TestCase
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

        $output = $this->app->make(
            OutputStyle::class,
            [
                'input' => new ArrayInput(['--force' => true]),
                'output' => new NullOutput(),
            ]
        );

        $this->command->setOutput($output);

        $result = $this->command->handle($this->migrator);

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

        $input = new ArrayInput(
            ['--force' => true, 'fileName' => 'test_file_name'],
            $this->command->getDefinition()
        );

        $this->command->setInput($input);

        $this->command->setOutput(
            $this->app->make(
                OutputStyle::class,
                [
                    'input' => $input,
                    'output' => new NullOutput(),
                ]
            )
        );

        $result = $this->command->handle($this->migrator);

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

        $input = new ArrayInput(
            ['--force' => true],
            $this->command->getDefinition()
        );

        $this->command->setInput($input);

        $this->command->setOutput(
            $this->app->make(
                OutputStyle::class,
                [
                    'input' => $input,
                    'output' => new NullOutput(),
                ]
            )
        );

        $result = $this->command->handle($this->migrator);

        $this->assertSame(0, $result);
    }
}
