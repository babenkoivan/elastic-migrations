<?php declare(strict_types=1);

use Elastic\Elasticsearch\Client;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateTestIndex implements MigrationInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function up(): void
    {
        Index::create('test');

        $this->client->indices()->clearCache([
            'index' => 'test',
        ]);
    }

    public function down(): void
    {
        Index::drop('test');
    }
}
