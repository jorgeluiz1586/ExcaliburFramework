<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers;

use Excalibur\Framework\Http\Server\Helpers\Interfaces\OpenCheckerInterface;

class OpenBotChecker implements OpenCheckerInterface
{
    public static function check(string $value): bool
    {
        return
            str_contains(strtolower($value), "bot") ||
            str_contains(strtolower($value), "google") ||
            str_contains(strtolower($value), "brave") ||
            str_contains(strtolower($value), "duckduckgo") ||
            str_contains(strtolower($value), "bing") ||
            str_contains(strtolower($value), "yandex") ||
            str_contains(strtolower($value), "yahoo");
    }
}
