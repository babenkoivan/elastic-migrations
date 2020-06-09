<?php
declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Adapters;

use ElasticAdapter\Indices\Index;
use ElasticAdapter\Indices\IndexManager;
use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use ElasticMigrations\Adapters\IndexManagerAdapter;
use ElasticMigrations\Tests\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_index_can_be_created_without_modifier(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index($indexNamePrefix.$indexName));

        $this->indexManagerAdapter->create($indexName);
    }

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_index_can_be_created_with_modifier(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = function (Mapping $mapping, Settings $settings) {
            $mapping->text('title');
            $settings->numberOfReplicas(2);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index(
                $indexNamePrefix.$indexName,
                (new Mapping())->text('title'),
                (new Settings())->numberOfReplicas(2)
            ));

        $this->indexManagerAdapter->create($indexName, $modifier);
    }

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_index_can_be_created_only_if_it_does_not_exist(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexNamePrefix.$indexName)
            ->willReturn(false);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index($indexNamePrefix.$indexName));

        $this->indexManagerAdapter->createIfNotExists($indexName);
    }

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_mapping_can_be_updated(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = function (Mapping $mapping) {
            $mapping->disableSource()->text('title');
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putMapping')
            ->with(
                $indexNamePrefix.$indexName,
                (new Mapping())->disableSource()->text('title')
            );

        $this->indexManagerAdapter->putMapping($indexName, $modifier);
    }

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_settings_can_be_updated(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = function (Settings $settings) {
            $settings->numberOfReplicas(2)->refreshInterval(-1);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettings')
            ->with(
                $indexNamePrefix.$indexName,
                (new Settings())->numberOfReplicas(2)->refreshInterval(-1)
            );

        $this->indexManagerAdapter->putSettings($indexName, $modifier);
    }

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_settings_can_be_updated_in_a_hard_way(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = function (Settings $settings) {
            $settings->numberOfReplicas(2);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('close')
            ->with($indexNamePrefix.$indexName);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettings')
            ->with(
                $indexNamePrefix.$indexName,
                (new Settings())->numberOfReplicas(2)
            );

        $this->indexManagerMock
            ->expects($this->once())
            ->method('open')
            ->with($indexNamePrefix.$indexName);

        $this->indexManagerAdapter->putSettingsHard($indexName, $modifier);
    }

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_index_can_be_dropped(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('drop')
            ->with($indexNamePrefix.$indexName);

        $this->indexManagerAdapter->drop($indexName);
    }

    /**
     * @dataProvider indexNamePrefixProvider
     */
    public function test_index_can_be_dropped_only_if_exists(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexNamePrefix.$indexName)
            ->willReturn(true);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('drop')
            ->with($indexNamePrefix.$indexName);

        $this->indexManagerAdapter->dropIfExists($indexName);
    }

    public function indexNamePrefixProvider(): array
    {
        return [
            'no prefix' => [''],
            'short prefix' => ['foo_'],
            'long prefix' => ['foo_bar_'],
        ];
    }
}
