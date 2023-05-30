<?php

declare(strict_types=1);

namespace Modular\Framework\Database\Interfaces;

interface DatabaseInterface
{
    public function getConnection(): DatabaseConnectionInterface;

    public function setConnection(object $config): void;
}
