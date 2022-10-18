<?php declare(strict_types=1);

namespace OpenSearch\Migrations;

interface ReadinessInterface
{
    public function isReady(): bool;
}
