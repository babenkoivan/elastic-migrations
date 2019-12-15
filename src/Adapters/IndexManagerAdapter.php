<?php
declare(strict_types=1);

namespace ElasticMigrations\Adapters;

use ElasticAdapter\Indices\Index;
use ElasticAdapter\Indices\IndexManager;
use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;

class IndexManagerAdapter
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager = $indexManager;
    }

    public function create(string $indexName, ?callable $modifier = null): self
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

    public function createIfNotExists(string $indexName, ?callable $modifier = null): self
    {
        if (!$this->indexManager->exists($indexName)) {
            $this->create($indexName, $modifier);
        }

        return $this;
    }

    public function putMapping(string $indexName, callable $modifier): self
    {
        $mapping = new Mapping();
        $modifier($mapping);
        $this->indexManager->putMapping($indexName, $mapping);

        return $this;
    }

    public function putSettings(string $indexName, callable $modifier): self
    {
        $settings = new Settings();
        $modifier($settings);
        $this->indexManager->putSettings($indexName, $settings);

        return $this;
    }

    public function putSettingsHard(string $indexName, callable $modifier): self
    {
        $this->indexManager->close($indexName);
        $this->putSettings($indexName, $modifier);
        $this->indexManager->open($indexName);

        return $this;
    }

    public function drop(string $indexName): self
    {
        $this->indexManager->drop($indexName);

        return $this;
    }

    public function dropIfExists(string $indexName): self
    {
        if ($this->indexManager->exists($indexName)) {
            $this->drop($indexName);
        }

        return $this;
    }
}
