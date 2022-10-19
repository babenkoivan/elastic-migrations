<?php declare(strict_types=1);

namespace OpenSearch\Migrations\Facades;

use Illuminate\Support\Facades\Facade;
use OpenSearch\Migrations\IndexManagerInterface;

/**
 * @method static self create(string $indexName, ?callable $modifier = null)
 * @method static self createRaw(string $indexName, ?array $mapping = null, ?array $settings = null)
 * @method static self createIfNotExists(string $indexName, ?callable $modifier = null)
 * @method static self createIfNotExistsRaw(string $indexName, ?array $mapping = null, ?array $settings = null)
 * @method static self putMapping(string $indexName, callable $modifier)
 * @method static self putMappingRaw(string $indexName, array $mapping)
 * @method static self putSettings(string $indexName, callable $modifier)
 * @method static self putSettingsRaw(string $indexName, array $settings)
 * @method static self pushSettings(string $indexName, callable $modifier)
 * @method static self pushSettingsRaw(string $indexName, array $settings)
 * @method static self drop(string $indexName)
 * @method static self dropIfExists(string $indexName)
 * @method static self putAlias(string $indexName, string $aliasName, array $filter = null)
 * @method static self deleteAlias(string $indexName, string $aliasName)
 * @method static self connection(string $connection)
 */
final class Index extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return IndexManagerInterface::class;
    }
}
