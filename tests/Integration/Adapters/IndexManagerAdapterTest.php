<?php declare(strict_types=1);

namespace Elastic\Migrations\Tests\Integration\Adapters;

use Elastic\Adapter\Indices\Index;
use Elastic\Adapter\Indices\IndexManager;
use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Adapters\IndexManagerAdapter;
use Elastic\Migrations\Tests\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Elastic\Migrations\Adapters\IndexManagerAdapter
 */
final class IndexManagerAdapterTest extends TestCase
{
    private MockObject $indexManagerMock;
    private IndexManagerAdapter $indexManagerAdapter;

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
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index($indexNamePrefix . $indexName));

        $this->indexManagerAdapter->create($indexName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_created_with_modifier(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';

        $modifier = static function (Mapping $mapping, Settings $settings) {
            $mapping->text('title');
            $settings->index(['number_of_replicas' => 2]);
        };

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index(
                $indexNamePrefix . $indexName,
                (new Mapping())->text('title'),
                (new Settings())->index(['number_of_replicas' => 2])
            ));

        $this->indexManagerAdapter->create($indexName, $modifier);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_created_with_raw_mapping(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';

        $mapping = [
            'properties' => [
                'title' => [
                    'type' => 'text',
                ],
            ],
        ];

        $this->indexManagerMock
            ->expects($this->once())
            ->method('createRaw')
            ->with($indexNamePrefix . $indexName, $mapping);

        $this->indexManagerAdapter->createRaw($indexName, $mapping);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_with_modifier_can_be_created_only_if_it_does_not_exist(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexNamePrefix . $indexName)
            ->willReturn(false);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(new Index($indexNamePrefix . $indexName));

        $this->indexManagerAdapter->createIfNotExists($indexName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_with_raw_mapping_can_be_created_only_if_it_does_not_exist(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';

        $mapping = [
            'properties' => [
                'title' => [
                    'type' => 'text',
                ],
            ],
        ];

        $this->indexManagerMock
            ->expects($this->once())
            ->method('exists')
            ->with($indexNamePrefix . $indexName)
            ->willReturn(false);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('createRaw')
            ->with($indexNamePrefix . $indexName, $mapping);

        $this->indexManagerAdapter->createIfNotExistsRaw($indexName, $mapping);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_mapping_can_be_updated_using_modifier(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

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
    public function test_mapping_can_be_updated_using_raw_input(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';

        $mapping = [
            'properties' => [
                'title' => ['type' => 'text'],
            ],
        ];

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putMappingRaw')
            ->with($indexNamePrefix . $indexName, $mapping);

        $this->indexManagerAdapter->putMappingRaw($indexName, $mapping);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_settings_can_be_updated_using_modifier(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

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
    public function test_settings_can_be_updated_using_raw_input(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';
        $settings = ['number_of_replicas' => 2];

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettingsRaw')
            ->with($indexNamePrefix . $indexName, $settings);

        $this->indexManagerAdapter->putSettingsRaw($indexName, $settings);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_settings_can_be_pushed_using_modifier(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

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
    public function test_settings_can_be_pushed_using_raw_input(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

        $indexName = 'test';
        $settings = ['number_of_replicas' => 2];

        $this->indexManagerMock
            ->expects($this->once())
            ->method('close')
            ->with($indexNamePrefix . $indexName);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putSettingsRaw')
            ->with($indexNamePrefix . $indexName, $settings);

        $this->indexManagerMock
            ->expects($this->once())
            ->method('open')
            ->with($indexNamePrefix . $indexName);

        $this->indexManagerAdapter->pushSettingsRaw($indexName, $settings);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_index_can_be_dropped(string $indexNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

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
        $this->config->set('elastic.migrations.prefixes.index', $indexNamePrefix);

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
        $this->config->set('elastic.migrations.prefixes.alias', $aliasNamePrefix);

        $indexName = 'foo';
        $aliasName = 'bar';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('putAliasRaw')
            ->with($indexName, $aliasNamePrefix . $aliasName);

        $this->indexManagerAdapter->putAlias($indexName, $aliasName);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function test_alias_can_be_deleted(string $aliasNamePrefix): void
    {
        $this->config->set('elastic.migrations.prefixes.alias', $aliasNamePrefix);

        $indexName = 'foo';
        $aliasName = 'bar';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('deleteAlias')
            ->with($indexName, $aliasNamePrefix . $aliasName);

        $this->indexManagerAdapter->deleteAlias($indexName, $aliasName);
    }

    public function test_connection_can_be_changed(): void
    {
        $connection = 'test';

        $this->indexManagerMock
            ->expects($this->once())
            ->method('connection')
            ->with($connection);

        $this->indexManagerAdapter->connection($connection);
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
