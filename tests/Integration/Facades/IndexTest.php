<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Facades;

use ElasticMigrations\Adapters\IndexManagerAdapter;
use ElasticMigrations\Facades\Index;
use ElasticMigrations\Tests\Integration\TestCase;

/**
 * @covers \ElasticMigrations\Facades\Index
 * @uses   \ElasticMigrations\Adapters\IndexManagerAdapter
 * @uses   \ElasticMigrations\ServiceProvider
 */
final class IndexTest extends TestCase
{
    public function test_facade_instantiates_instance_of_correct_type(): void
    {
        $this->assertInstanceOf(IndexManagerAdapter::class, Index::getFacadeRoot());
    }
}
