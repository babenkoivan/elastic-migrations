<?php
declare(strict_types=1);

namespace ElasticMigrations;

interface IndexManagerInterface
{
    public function create(string $indexName, ?callable $modifier = null): self;

    public function createIfNotExists(string $indexName, ?callable $modifier = null): self;

    public function putMapping(string $indexName, callable $modifier): self;

    public function putSettings(string $indexName, callable $modifier): self;

    public function putSettingsHard(string $indexName, callable $modifier): self;

    public function drop(string $indexName): self;

    public function dropIfExists(string $indexName): self;
}
