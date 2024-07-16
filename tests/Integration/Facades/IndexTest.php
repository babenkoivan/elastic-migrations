<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration\Facades;

use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\IndexManagerInterface;
use Elastic\Migrations\Tests\Integration\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Index::class)]
final class IndexTest extends TestCase
{
    public function test_facade_instantiates_instance_of_correct_type(): void
    {
        $this->assertInstanceOf(IndexManagerInterface::class, Index::getFacadeRoot());
    }
}
