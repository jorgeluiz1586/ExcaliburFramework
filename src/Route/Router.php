<?php

declare(strict_types=1);

namespace Excalibur\Framework\Route;

class Router
{
    private static $routes = [];
    private static $webRoutes = [];

    public static function searchRoute(string $method, object $handle)
    {
        $routeObject = (object) self::getParamsInRoute("api", $method, $handle->route);

        return (object) [
            "route" => count($routeObject->route) > 0 ? $routeObject->route[0] : null,
            "params" => (object) [...$routeObject->params, ...$handle->params],
        ];
    }

    
    public static function searchWebRoute(string $method, object $handle)
    {
        $routeObject = (object) self::getParamsInRoute("web", $method, $handle->route);

        return (object) [
            "route" => count($routeObject->route) > 0 ? $routeObject->route[0] : null,
            "params" => (object) [...$routeObject->params, ...$handle->params],
        ];
    }

    public static function checkIfHasFinalDash($path)
    {
        $processedPath = $path;
        if (strlen($path) >= 3) {
            $pathParts = array_reverse(str_split($path));
            if ($pathParts[0] === "/") {
                $processedPath = implode(array_reverse(array_splice($pathParts, 1)));
            }
        }

        return $processedPath;
    }

    private static function getParamsInRoute(string $type, string $method, string $path)
    {
        $params = [];
        return [
            "route" => [
                ...array_filter($type === "web" ? self::$webRoutes : self::$routes,
                function ($route) use ($method, $path, &$params) {
                    return self::checkParamsInRoute(["route" => $route, "path" => $path], $method, $params);
                })
            ],
            "params" => &$params
        ];
    }


    private static function checkParamsInRoute(array $routeAndPath, string $method, array &$params)
    {
        $route = $routeAndPath['route'];
        $path = $routeAndPath['path'];
        if ($route['method'] === $method) {
            $routePathArray = explode("/", $route['uri']);
            $requestPathArray = explode("/", $path);
            $pathArray = [];
            if (count($routePathArray) === count($requestPathArray)) {
                foreach ($routePathArray as $routeIndex => $routePathSlice) {
                    if (
                        strpos($routePathSlice, "{") === 0 &&
                        strpos($routePathSlice, "}") === (strlen($routePathSlice) - 1)
                    ) {
                        $params["".str_replace("}", "", str_replace("{", "", $routePathSlice)).""]
                            = $requestPathArray[$routeIndex];
                        $pathArray[] = true;
                    } else {
                        $pathArray[] = $requestPathArray[$routeIndex] === $routePathSlice;
                    }
                }
                if (!in_array(false, $pathArray)) {
                    return $route;
                }
            }
        }
    }

    public static function getRoutes(): array
    {
        return [...self::$routes];
    }

    public static function getWebRoutes(): array
    {
        return [...self::$webRoutes];
    }

    public static function setRoute(
        string $method, string $path, string|null $controller, string|callable $action): void
    {
        $controllerObject = null;
        if ($controller !== null) {
            $controllerObject = "Api\\Controllers\\".$controller;
            $service = "Application\\Services\\".
                        str_replace("Controller", "Service", $controller);
            $repository = "Infrastructure\\Data\\Repositories\\".
                        str_replace("Controller", "Repository", $controller);
            $controllerObject = new $controllerObject(new $service(new $repository()));
        }

        array_push(self::$routes,
            ["method" => $method, "uri" => $path, "controller" => $controllerObject,
                                    "action" => $action, "middleware" => ""]);
    }


    public static function setWebRoute(string $method, string $path, string|null $controller, string|callable $action): void
    {
        $controllerObject = null;
        if ($controller !== null) {
            $controllerObject = "WebUI\\Controllers\\".$controller;
            $service = "Application\\Services\\".
                        str_replace("Controller", "Service", $controller);
            $repository = "Infrastructure\\Data\\Repositories\\".
                        str_replace("Controller", "Repository", $controller);
            $controllerObject = new $controllerObject(new $service(new $repository()));
        }

        array_push(self::$webRoutes,
            ["method" => $method, "uri" => $path, "controller" => $controllerObject,
                                    "action" => $action, "middleware" => ""]);
    }
    
    public static function setMiddlewareInRoute(string $path, string $middleware)
    {
        foreach(self::getRoutes() as $key => $route) {
            $pathFormatted = $path === "/" ? "" : $path;
            if ($route["uri"] === "/api".$pathFormatted) {
                self::$routes[$key]["middleware"] = $middleware;
            }
        }
    }

    public static function setMiddlewareInWebRoute(string $path, string $middleware)
    {
        foreach(self::getWebRoutes() as $key => $route) {
            if ($route["uri"] === $path) {
                self::$webRoutes[$key]["middleware"] = $middleware;
            }
        }
    }

}
