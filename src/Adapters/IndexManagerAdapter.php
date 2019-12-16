<?php
declare(strict_types=1);

namespace ElasticMigrations\Adapters;

use ElasticAdapter\Indices\Index;
use ElasticAdapter\Indices\IndexManager;
use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;
use ElasticMigrations\Contracts\IndexManagerInterface;

class IndexManagerAdapter implements IndexManagerInterface
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager = $indexManager;
    }

    public function create(string $indexName, ?callable $modifier = null): IndexManagerInterface
    {
        if (isset($modifier)) {
            $mapping = new Mapping();
            $settings = new Settings();

            $modifier($mapping, $settings);

            $index = new Index($indexName, $mapping, $settings);
        } else {
            $index = new Index($indexName);
        }

        $this->indexManager->create($index);

        return $this;
    }

    public function createIfNotExists(string $indexName, ?callable $modifier = null): IndexManagerInterface
    {
        if (!$this->indexManager->exists($indexName)) {
            $this->create($indexName, $modifier);
        }

        return $this;
    }

    public function putMapping(string $indexName, callable $modifier): IndexManagerInterface
    {
        $mapping = new Mapping();
        $modifier($mapping);
        $this->indexManager->putMapping($indexName, $mapping);

        return $this;
    }

    public function putSettings(string $indexName, callable $modifier): IndexManagerInterface
    {
        $settings = new Settings();
        $modifier($settings);
        $this->indexManager->putSettings($indexName, $settings);

        return $this;
    }

    public function putSettingsHard(string $indexName, callable $modifier): IndexManagerInterface
    {
        $this->indexManager->close($indexName);
        $this->putSettings($indexName, $modifier);
        $this->indexManager->open($indexName);

        return $this;
    }

    public function drop(string $indexName): IndexManagerInterface
    {
        $this->indexManager->drop($indexName);

        return $this;
    }

    public function dropIfExists(string $indexName): IndexManagerInterface
    {
        if ($this->indexManager->exists($indexName)) {
            $this->drop($indexName);
        }

        return $this;
    }
}
