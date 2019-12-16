<?php
declare(strict_types=1);

namespace ElasticMigrations\Contracts;

interface ReadinessInterface
{
    public function isReady(): bool;
}
