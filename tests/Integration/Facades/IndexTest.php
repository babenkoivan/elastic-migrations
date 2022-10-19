<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Tests\Integration\Facades;

use OpenSearch\Migrations\Facades\Index;
use OpenSearch\Migrations\IndexManagerInterface;
use OpenSearch\Migrations\Tests\Integration\TestCase;

/**
 * @covers \OpenSearch\Migrations\Facades\Index
 */
final class IndexTest extends TestCase
{
    public function test_facade_instantiates_instance_of_correct_type(): void
    {
        $this->assertInstanceOf(IndexManagerInterface::class, Index::getFacadeRoot());
    }
}
