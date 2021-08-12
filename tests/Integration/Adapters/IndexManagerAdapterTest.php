<?php declare(strict_types=1);

namespace ElasticMigrations\Tests\Integration\Adapters;

use ElasticAdapter\Indices\Alias;
use ElasticAdapter\Indices\IndexBlueprint;
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
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_created_without_modifier(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new IndexBlueprint($indexNamePrefix . $indexName));

        $this->indexManagerAdapter->create($indexName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_created_with_modifier(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = static function (Mapping $mapping, Settings $settings) {
            $mapping->text('title');
            $settings->index(['number_of_replicas' => 2]);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new IndexBlueprint(
                $indexNamePrefix . $indexName,
                (new Mapping())->text('title'),
                (new Settings())->index(['number_of_replicas' => 2])
            ));

        $this->indexManagerAdapter->create($indexName, $modifier);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_created_only_if_it_does_not_exist(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexNamePrefix . $indexName)
            ->willReturn(false);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new IndexBlueprint($indexNamePrefix . $indexName));

        $this->indexManagerAdapter->createIfNotExists($indexName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_mapping_can_be_updated(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = static function (Mapping $mapping) {
            $mapping->disableSource()->text('title');
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putMapping')
            ->with(
                $indexNamePrefix . $indexName,
                (new Mapping())->disableSource()->text('title')
            );

        $this->indexManagerAdapter->putMapping($indexName, $modifier);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_settings_can_be_updated(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = static function (Settings $settings) {
            $settings->index(['number_of_replicas' => 2, 'refresh_interval' => -1]);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettings')
            ->with(
                $indexNamePrefix . $indexName,
                (new Settings())->index(['number_of_replicas' => 2, 'refresh_interval' => -1])
            );

        $this->indexManagerAdapter->putSettings($indexName, $modifier);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_settings_can_be_pushed(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $modifier = static function (Settings $settings) {
            $settings->index(['number_of_replicas' => 2]);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('close')
            ->with($indexNamePrefix . $indexName);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettings')
            ->with(
                $indexNamePrefix . $indexName,
                (new Settings())->index(['number_of_replicas' => 2])
            );

        $this->indexManagerMock
            ->expects($this->once())
            ->method('open')
            ->with($indexNamePrefix . $indexName);

        $this->indexManagerAdapter->pushSettings($indexName, $modifier);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_dropped(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('drop')
            ->with($indexNamePrefix . $indexName);

        $this->indexManagerAdapter->drop($indexName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_dropped_only_if_exists(string $indexNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.index_name_prefix', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexNamePrefix . $indexName)
            ->willReturn(true);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('drop')
            ->with($indexNamePrefix . $indexName);

        $this->indexManagerAdapter->dropIfExists($indexName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_alias_can_be_created(string $aliasNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.alias_name_prefix', $aliasNamePrefix);

        $indexName = 'foo';
        $aliasName = 'bar';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putAlias')
            ->with($indexName, new Alias($aliasNamePrefix . $aliasName));

        $this->indexManagerAdapter->putAlias($indexName, $aliasName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_alias_can_be_deleted(string $aliasNamePrefix): void
    {
        $this->app['config']->set('elastic.migrations.alias_name_prefix', $aliasNamePrefix);

        $indexName = 'foo';
        $aliasName = 'bar';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('deleteAlias')
            ->with($indexName, $aliasNamePrefix . $aliasName);

        $this->indexManagerAdapter->deleteAlias($indexName, $aliasName);
    }

    public function prefixProvider(): array
    {
        return [
            'no prefix' => [''],
            'short prefix' => ['foo_'],
            'long prefix' => ['foo_bar_'],
        ];
    }
}
