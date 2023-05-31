<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers;

use Excalibur\Framework\Http\Server\Helpers\Interfaces\CheckerInterface;

class SpaChecker implements CheckerInterface
{
    public static function check(): bool
    {
        return str_contains(strtolower($_SERVER['REQUEST_URI']), "spa=true");
    }
}
