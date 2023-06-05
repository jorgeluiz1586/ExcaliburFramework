<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers;

use Excalibur\Framework\Http\Server\Helpers\Interfaces\OpenCheckerInterface;

class OpenAssetChecker implements OpenCheckerInterface
{
    public static function check(string $value): bool
    {
        return
            str_contains($value, "/scripts") ||
            str_contains($value, "/css") ||
            str_contains($value, "/favicon");
    }
}
