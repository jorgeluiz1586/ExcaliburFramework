<?php

declare(strict_types=1);

namespace Excalibur\Framework\Middlewares\Interfaces;

interface OpenMiddlewareHandlerInterface
{
    public function handle(?string $middleware): void;
}
