<?php

declare(strict_types=1);

namespace Excalibur\Framework\Route;

use Excalibur\Framework\Route\Router;
use Excalibur\Framework\Middlewares\Handler;

class Route
{
    public static function get(string $path, callable|string|array $handle): Handler
    {
        if (gettype($handle) === "string") {
           self::getControllerAndActionFromString($path, $handle, "GET");
        } elseif (is_callable($handle)) {
            Router::setRoute("GET", "/api".($path === "/" ? "" : $path), null, $handle);
        } elseif (gettype($handle === "array") && count($handle) === 2) {
            Router::setRoute("GET", "/api".($path === "/" ? "" : $path), $handle[0], $handle[1]);
        } else {
            throw (new \Exception("Error! Route is invalid"));
        }

        $middleware = new Handler();
        $middleware->type = "api";
        $middleware->path = $path;
        return $middleware;
    }

    public static function post(string $path, callable|string|array $handle): Handler
    {
        if (gettype($handle) === "string") {
           self::getControllerAndActionFromString($path, $handle, "POST");
        } elseif (is_callable($handle)) {
           Router::setRoute("POST", "/api".($path === "/" ? "" : $path), null, $handle);
        } elseif (gettype($handle === "array")) {
            if (count($handle) === 2) {
               Router::setRoute("POST", "/api".($path === "/" ? "" : $path), $handle[0], $handle[1]);
            }
        } else {
            throw (new \Exception("Error! Route is invalid"));
        }

        $middleware = new Handler();
        $middleware->type = "api";
        $middleware->path = $path;
        return $middleware;
    }

    public static function put(string $path, callable|string|array $handle): Handler
    {
        if (gettype($handle) === "string") {
            self::getControllerAndActionFromString($path, $handle, "PUT");
        } elseif (is_callable($handle)) {
            Router::setRoute("PUT", "/api".($path === "/" ? "" : $path), null, $handle);
        } elseif (gettype($handle === "array")) {
            if (count($handle) === 2) {
                Router::setRoute("PUT", "/api".($path === "/" ? "" : $path), $handle[0], $handle[1]);
            }
        } else {
            throw (new \Exception("Error! Route is invalid"));
        }

        $middleware = new Handler();
        $middleware->type = "api";
        $middleware->path = $path;
        return $middleware;
    }

    public static function patch(string $path, callable|string|array $handle): Handler
    {
        if (gettype($handle) === "string") {
            self::getControllerAndActionFromString($path, $handle, "PATCH");
        } elseif (is_callable($handle)) {
            Router::setRoute("PATCH", "/api".($path === "/" ? "" : $path), null, $handle);
        } elseif (gettype($handle === "array")) {
            if (count($handle) === 2) {
                Router::setRoute("PATCH", "/api".($path === "/" ? "" : $path), $handle[0], $handle[1]);
            }
        } else {
            throw (new \Exception("Error! Route is invalid"));
        }

        $middleware = new Handler();
        $middleware->type = "api";
        $middleware->path = $path;
        return $middleware;
    }

    public static function delete(string $path, callable|string|array $handle): Handler
    {
        if (gettype($handle) === "string") {
            self::getControllerAndActionFromString($path, $handle, "DELETE");
        } elseif (is_callable($handle)) {
            Router::setRoute("DELETE", "/api".($path === "/" ? "" : $path), null, $handle);
        } elseif (gettype($handle === "array")) {
            if (count($handle) === 2) {
                Router::setRoute("DELETE", "/api".($path === "/" ? "" : $path), $handle[0], $handle[1]);
            }
        } else {
            throw (new \Exception("Error! Route is invalid"));
        }

        $middleware = new Handler();
        $middleware->type = "api";
        $middleware->path = $path;
        return $middleware;
    }

    public static function apiResource(string $path, callable|string|array $handle): Handler
    {
        if (gettype($handle) === "string") {
            $controller = "Application\\Controllers\\".explode("@", $handle)[0];
            $action = explode("@", $handle)[1];

            Router::setRoute("", "/api".($path === "/" ? "" : $path), $controller, $action);
        }

        $middleware = new Handler();
        $middleware->type = "api";
        $middleware->path = $path;
        return $middleware;
    }

    public static function resource(string $path, callable|string|array $handle): Handler
    {
        if (gettype($handle) === "string") {
            $controller = "Application\\Controllers\\".explode("@", $handle)[0];
            $action = explode("@", $handle)[1];

            Router::setRoute("", "/api".($path === "/" ? "" : $path), $controller, $action);
        }

        $middleware = new Handler();
        $middleware->type = "api";
        $middleware->path = $path;
        return $middleware;
    }

    private static function getControllerAndActionFromString(string $path, string $handle, string $method): void
    {
        if ($handle !== "" && str_contains($handle, "@")) {
            $controller = "Application\\Controllers\\".explode("@", $handle)[0];
            $action = explode("@", $handle)[1];
            $handleObj = (object) ["controller" => $controller, "action" => $action];
            Router::setRoute("$method", "/api".($path === "/" ? "" : $path),
                                $handleObj->controller, $handleObj->action);
        } else {
            Router::setWebRoute("$method", $path, $handle);
        }
    }
}
