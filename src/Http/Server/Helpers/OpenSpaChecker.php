<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers;

use Excalibur\Framework\Http\Server\Helpers\Interfaces\OpenCheckerInterface;

class OpenSpaChecker implements OpenCheckerInterface
{
    public static function check(string $value): bool
    {
        return str_contains(strtolower($value), "spa=true");
    }
}
