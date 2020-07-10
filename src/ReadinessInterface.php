<?php declare(strict_types=1);

namespace ElasticMigrations;

interface ReadinessInterface
{
    public function isReady(): bool;
}
