<?php

declare(strict_types=1);

namespace Excalibur\Framework\Database\Interfaces;

interface DatabaseInterface
{
    public function getConnection(): DatabaseConnectionInterface;

    public function setConnection(object $config): void;
}
