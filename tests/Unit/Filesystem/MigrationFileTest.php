<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Tests\Unit\Filesystem;

use OpenSearch\Migrations\Filesystem\MigrationFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenSearch\Migrations\Filesystem\MigrationFile
 */
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
