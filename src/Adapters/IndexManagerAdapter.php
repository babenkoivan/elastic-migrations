<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Adapters;

use OpenSearch\Adapter\Indices\Index;
use OpenSearch\Adapter\Indices\IndexManager;
use OpenSearch\Adapter\Indices\Mapping;
use OpenSearch\Adapter\Indices\Settings;
use OpenSearch\Migrations\IndexManagerInterface;
use function OpenSearch\Migrations\prefix_alias_name;
use function OpenSearch\Migrations\prefix_index_name;

class IndexManagerAdapter implements IndexManagerInterface
{
    private IndexManager $indexManager;

    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager = $indexManager;
    }

    public function create(string $indexName, ?callable $modifier = null): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        if (isset($modifier)) {
            $mapping = new Mapping();
            $settings = new Settings();

            $modifier($mapping, $settings);

            $index = new Index($prefixedIndexName, $mapping, $settings);
        } else {
            $index = new Index($prefixedIndexName);
        }

        $this->indexManager->create($index);

        return $this;
    }

    public function createRaw(string $indexName, ?array $mapping = null, ?array $settings = null): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $this->indexManager->createRaw($prefixedIndexName, $mapping, $settings);

        return $this;
    }

    public function createIfNotExists(string $indexName, ?callable $modifier = null): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        if (!$this->indexManager->exists($prefixedIndexName)) {
            $this->create($indexName, $modifier);
        }

        return $this;
    }

    public function createIfNotExistsRaw(
        string $indexName,
        ?array $mapping = null,
        ?array $settings = null
    ): IndexManagerInterface {
        $prefixedIndexName = prefix_index_name($indexName);

        if (!$this->indexManager->exists($prefixedIndexName)) {
            $this->createRaw($indexName, $mapping, $settings);
        }

        return $this;
    }

    public function putMapping(string $indexName, callable $modifier): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $mapping = new Mapping();
        $modifier($mapping);

        $this->indexManager->putMapping($prefixedIndexName, $mapping);

        return $this;
    }

    public function putMappingRaw(string $indexName, array $mapping): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $this->indexManager->putMappingRaw($prefixedIndexName, $mapping);

        return $this;
    }

    public function putSettings(string $indexName, callable $modifier): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $settings = new Settings();
        $modifier($settings);

        $this->indexManager->putSettings($prefixedIndexName, $settings);

        return $this;
    }

    public function putSettingsRaw(string $indexName, array $settings): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $this->indexManager->putSettingsRaw($prefixedIndexName, $settings);

        return $this;
    }

    public function pushSettings(string $indexName, callable $modifier): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $this->indexManager->close($prefixedIndexName);
        $this->putSettings($indexName, $modifier);
        $this->indexManager->open($prefixedIndexName);

        return $this;
    }

    public function pushSettingsRaw(string $indexName, array $settings): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $this->indexManager->close($prefixedIndexName);
        $this->putSettingsRaw($indexName, $settings);
        $this->indexManager->open($prefixedIndexName);

        return $this;
    }

    public function drop(string $indexName): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        $this->indexManager->drop($prefixedIndexName);

        return $this;
    }

    public function dropIfExists(string $indexName): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);

        if ($this->indexManager->exists($prefixedIndexName)) {
            $this->drop($indexName);
        }

        return $this;
    }

    public function putAlias(string $indexName, string $aliasName, array $settings = null): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);
        $prefixedAliasName = prefix_alias_name($aliasName);

        $this->indexManager->putAliasRaw($prefixedIndexName, $prefixedAliasName, $settings);

        return $this;
    }

    public function deleteAlias(string $indexName, string $aliasName): IndexManagerInterface
    {
        $prefixedIndexName = prefix_index_name($indexName);
        $prefixedAliasName = prefix_alias_name($aliasName);

        $this->indexManager->deleteAlias($prefixedIndexName, $prefixedAliasName);

        return $this;
    }

    public function connection(string $connection): IndexManagerInterface
    {
        $self = clone $this;
        $self->indexManager = $self->indexManager->connection($connection);
        return $self;
    }
}
