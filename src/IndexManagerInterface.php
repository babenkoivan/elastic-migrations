<?php declare(strict_types=1);

namespace OpenSearch\Migrations;

interface IndexManagerInterface
{
    public function create(string $indexName, ?callable $modifier = null): self;

    public function createRaw(string $indexName, ?array $mapping = null, ?array $settings = null): self;

    public function createIfNotExists(string $indexName, ?callable $modifier = null): self;

    public function createIfNotExistsRaw(string $indexName, ?array $mapping = null, ?array $settings = null): self;

    public function putMapping(string $indexName, callable $modifier): self;

    public function putMappingRaw(string $indexName, array $mapping): self;

    public function putSettings(string $indexName, callable $modifier): self;

    public function putSettingsRaw(string $indexName, array $settings): self;

    public function pushSettings(string $indexName, callable $modifier): self;

    public function pushSettingsRaw(string $indexName, array $settings): self;

    public function drop(string $indexName): self;

    public function dropIfExists(string $indexName): self;

    public function connection(string $connection): self;
}
