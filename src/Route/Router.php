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
            $service = "Application\\Services\\".
                        str_replace("Controller", "Service", array_reverse(explode("\\", $controller))[0]);
            $repository = "Infrastructure\\Data\\Repositories\\".
                        str_replace("Controller", "Repository", array_reverse(explode("\\", $controller))[0]);
            $entity = "Domain\\Entities\\".str_replace("Controller", "", array_reverse(explode("\\", $controller))[0]);
            $controllerObject = new $controller(new $service(new $repository(new $entity())));
        }

        array_push(self::$routes,
            ["method" => $method, "uri" => $path, "controller" => $controllerObject,
                                    "action" => $action, "middleware" => ""]);
    }


    public static function setWebRoute(string $method, string $path, string $viewPath): void
    {
        $view = "";
        if (str_contains($path, '_')) {
            $view = implode("", array_map(function ($item)
            {
                return ucfirst(strtolower($item));
            }, explode("_", $path)));
        } elseif (str_contains($path, '-')) {
            $view = implode("", array_map(function ($item)
            {
                return ucfirst(strtolower($item));
            }, explode("-", $path)));
        } else {
            if ($path === "/") {
                $view = "/".ucfirst(strtolower("Home"));
            } else {
                $view = "/".ucfirst(implode("", (explode("/", strtolower($path)))));
            }
        }

        if ($viewPath !== "" && explode("/", $viewPath)[1] === "Views") {
            array_push(self::$webRoutes, ["uri" => $path, "method" => $method,
            "view" => "/".array_reverse(explode("/", $viewPath))[0]]);
        } else {
            array_push(self::$webRoutes, ["uri" => $path, "method" => $method, "view" => $view."View"]);
        }
    }
    
    public static function setMiddlewareInRoute(string $path, string $middleware)
    {
        print_r(self::$routes[$key]);
        die();
        foreach(self::getRoutes() as $key => $route) {
            if ($route["uri"] === $path) {
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
