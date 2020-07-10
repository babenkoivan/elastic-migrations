<?php declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Facades;

use ElasticMigrations\Facades\Index;
use ElasticMigrations\IndexManagerInterface;
use ElasticMigrations\Tests\Integration\TestCase;

/**
 * @covers \ElasticMigrations\Facades\Index
 */
final class IndexTest extends TestCase
{
    public function test_facade_instantiates_instance_of_correct_type(): void
    {
        $this->assertInstanceOf(IndexManagerInterface::class, Index::getFacadeRoot());
    }
}
