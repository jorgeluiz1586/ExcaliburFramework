<?php

declare(strict_types=1);

namespace Excalibur\Framework\Middlewares;

use Excalibur\Framework\Route\Router;

class Handler
{
    public $type = "api";
    public $path = "";

    public function middleware(string|array $middlewareName = ""): void {
        if ($this->type === "web") {
            Router::setMiddlewareInWebRoute($this->path, $middlewareName);
            
        }

        Router::setMiddlewareInRoutes($this->path, $middlewareName);
    }
}
