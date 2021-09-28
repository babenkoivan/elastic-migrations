<?php declare(strict_types=1);

use ElasticMigrations\Facades\Index;
use ElasticMigrations\MigrationInterface;
use Elasticsearch\Client;

final class CreateAnotherTestIndex implements MigrationInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function up(): void
    {
        Index::create('another_test');

        $this->client->indices()->clearCache([
            'index' => 'another_test',
        ]);
    }

    public function down(): void
    {
        Index::drop('another_test');
    }
}
