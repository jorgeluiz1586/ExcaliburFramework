<?php

declare(strict_types=1);

class ControllerPrototype
{
    private static array $controllers = [];

    public static function setController(string $controllerClass, array $dependencies): void
    {
        $controller = new $controllerClass(
            $dependencies['service']($dependencies['repository']($dependencies['entity'])));

        $controllerFound = self::searchController(controller: $controller);

        if (count(self::$controllers) === 0 || count($controllerFound) === 0) {
            $controllerName = array_reverse(explode("\\", $controllerClass))[0];
            self::$controllers[$controllerName] = $controller;
        }
    }

    public static function getController(string $controllerName)
    {
        self::searchController(controllerName: $controllerName);
    }

    private static function searchController(?object $controller = new object(), ?string $controllerName = ""): array
    {
        return array_filter(self::$controllers, function ($controllerItem, $index) use ($controller, $controllerName)
        {
            if ($controllerItem === $controller || $index === $controllerName) {
                return $controllerItem;
            }
        });
    }
}
