<?php

declare(strict_types=1);

namespace Excalibur\Framework\Middlewares\Interfaces;

interface MiddlewareHandlerInterface
{
    public function handle(?string $middleware): void;
}
