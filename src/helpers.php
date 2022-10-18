<?php declare(strict_types=1);

namespace OpenSearch\Migrations;

function prefix_index_name(string $indexName): string
{
    return config('opensearch.migrations.prefixes.index') . $indexName;
}

function prefix_alias_name(string $aliasName): string
{
    return config('opensearch.migrations.prefixes.alias') . $aliasName;
}
