<?php declare(strict_types=1);

namespace Elastic\Migrations;

interface ReadinessInterface
{
    public function isReady(): bool;
}
