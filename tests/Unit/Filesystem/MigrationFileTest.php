<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Unit\Filesystem;

use Elastic\Migrations\Filesystem\MigrationFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elastic\Migrations\Filesystem\MigrationFile
 */
final class MigrationFileTest extends TestCase
{
    private const FULL_PATH = '/tmp/test.php';

    public function test_path_getter(): void
    {
        $this->assertSame(
            self::FULL_PATH,
            (new MigrationFile(self::FULL_PATH))->getPath()
        );
    }

    public function test_name_getter(): void
    {
        $this->assertSame(
            basename(self::FULL_PATH, '.php'),
            (new MigrationFile(self::FULL_PATH))->getName()
        );
    }
}
