<?php
declare(strict_types=1);

namespace ElasticMigrations\Facades;

use ElasticMigrations\Contracts\IndexManagerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static self create(string $indexName, ?callable $modifier = null)
 * @method static self createIfNotExists(string $indexName, ?callable $modifier = null)
 * @method static self putMapping(string $indexName, callable $modifier)
 * @method static self putSettings(string $indexName, callable $modifier)
 * @method static self putSettingsHard(string $indexName, callable $modifier)
 * @method static self drop(string $indexName)
 * @method static self dropIfExists(string $indexName)
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
