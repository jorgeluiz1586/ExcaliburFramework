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
            foreach(Router::getWebRoutes() as $key => $route) {
                if ($route["uri"] === $this->path) {
                    Router::getWebRoutes()[$key]["middleware"] = $middlewareName;
                }
            }
        }

        foreach(Router::getRoutes() as $key => $route) {
            if ($route["uri"] === $this->path) {
                Router::getRoutes()[$key]["middleware"] = $middlewareName;
            }
        }
    }
}
