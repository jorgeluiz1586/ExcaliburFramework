<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http2\Server\Helpers\Interfaces;

interface CheckerInterface
{
    public static function check(): bool;
}
