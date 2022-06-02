<?php declare(strict_types=1);

namespace ElasticMigrations;

function prefix_index_name(string $indexName): string
{
    return config('elastic.migrations.prefixes.index') . $indexName;
}

function prefix_alias_name(string $aliasName): string
{
    return config('elastic.migrations.prefixes.alias') . $aliasName;
}
