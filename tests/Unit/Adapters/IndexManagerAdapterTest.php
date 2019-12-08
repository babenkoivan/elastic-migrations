<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Unit\Adapters;

use ElasticAdapter\Indices\Index;
use ElasticAdapter\Indices\IndexManager;
use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use ElasticMigrations\Adapters\IndexManagerAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ElasticMigrations\Adapters\IndexManagerAdapter
 */
final class IndexManagerAdapterTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $indexManagerMock;
    /**
     * @var IndexManagerAdapter
     */
    private $indexManagerAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indexManagerMock = $this->createMock(IndexManager::class);
        $this->indexManagerAdapter = new IndexManagerAdapter($this->indexManagerMock);
    }

    public function test_index_can_be_created_without_modifier(): void
    {
        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index($indexName));

        $this->indexManagerAdapter->create($indexName);
    }

    public function test_index_can_be_created_with_modifier(): void
    {
        $indexName = 'test';

        $modifier = function (Mapping $mapping, Settings $settings) {
            $mapping->text('title');
            $settings->numberOfReplicas(2);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index(
                $indexName,
                (new Mapping())->text('title'),
                (new Settings())->numberOfReplicas(2)
            ));

        $this->indexManagerAdapter->create($indexName, $modifier);
    }

    public function test_index_can_be_created_only_if_it_doesnt_exist(): void
    {
        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexName)
            ->willReturn(false);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create');

        $this->indexManagerAdapter->createIfNotExists($indexName);
    }

    public function test_mapping_can_be_updated(): void
    {
        $indexName = 'test';

        $modifier = function (Mapping $mapping) {
            $mapping->disableSource()->text('title');
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putMapping')
            ->with(
                $indexName,
                (new Mapping())->disableSource()->text('title')
            );

        $this->indexManagerAdapter->putMapping($indexName, $modifier);
    }

    public function test_settings_can_be_updated(): void
    {
        $indexName = 'test';

        $modifier = function (Settings $settings) {
            $settings->numberOfReplicas(2)->refreshInterval(-1);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettings')
            ->with(
                $indexName,
                (new Settings())->numberOfReplicas(2)->refreshInterval(-1)
            );

        $this->indexManagerAdapter->putSettings($indexName, $modifier);
    }

    public function test_settings_can_be_updated_in_a_hard_way(): void
    {
        $indexName = 'test';

        $modifier = function (Settings $settings) {
            $settings->numberOfReplicas(2);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('close')
            ->with($indexName);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettings')
            ->with(
                $indexName,
                (new Settings())->numberOfReplicas(2)
            );

        $this->indexManagerMock
            ->expects($this->once())
            ->method('open')
            ->with($indexName);

        $this->indexManagerAdapter->putSettingsHard($indexName, $modifier);
    }

    public function test_index_can_be_dropped(): void
    {
        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('drop')
            ->with($indexName);

        $this->indexManagerAdapter->drop($indexName);
    }

    public function test_index_can_be_dropped_only_if_exists(): void
    {
        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexName)
            ->willReturn(true);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('drop')
            ->with($indexName);

        $this->indexManagerAdapter->dropIfExists($indexName);
    }
}
