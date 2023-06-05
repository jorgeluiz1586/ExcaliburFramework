<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers;

use Excalibur\Framework\Http\Server\Helpers\Interfaces\CheckerInterface;

class AssetChecker implements CheckerInterface
{
    public static function check(): bool
    {
        return
            str_contains($_SERVER["REQUEST_URI"], "/scripts") ||
            str_contains($_SERVER["REQUEST_URI"], "/css") ||
            str_contains($_SERVER["REQUEST_URI"], "/favicon");
    }
}
