<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers;

use Excalibur\Framework\Http\Server\Helpers\Interfaces\CheckerInterface;

class BotChecker implements CheckerInterface
{
    public static function check(): bool
    {
        return
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "bot") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "google") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "brave") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "duckduckgo") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "bing") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "yandex") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "yahoo");
    }
}
