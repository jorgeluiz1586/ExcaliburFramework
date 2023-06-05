<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers\Interfaces;

interface OpenCheckerInterface
{
    public static function check(string $value): bool;
}
