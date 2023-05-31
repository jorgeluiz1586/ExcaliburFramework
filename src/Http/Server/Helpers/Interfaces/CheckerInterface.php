<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server\Helpers\Interfaces;

interface CheckerInterface
{
    public static function check(): bool;
}
