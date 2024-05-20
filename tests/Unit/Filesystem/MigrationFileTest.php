<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Unit\Filesystem;

use Elastic\Migrations\Filesystem\MigrationFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MigrationFile::class)]
final class MigrationFileTest extends TestCase
{
    private const FULL_PATH = '/tmp/test.php';

    public function test_path_getter(): void
    {
        $this->assertSame(
            self::FULL_PATH,
            (new MigrationFile(self::FULL_PATH))->path()
        );
    }

    public function test_name_getter(): void
    {
        $this->assertSame(
            basename(self::FULL_PATH, MigrationFile::FILE_EXTENSION),
            (new MigrationFile(self::FULL_PATH))->name()
        );
    }
}
