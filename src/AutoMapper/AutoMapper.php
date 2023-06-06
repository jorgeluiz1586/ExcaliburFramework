<?php

declare(strict_types=1);

namespace Excalibur\Framework\AutoMapper;

class AutoMapper
{
    public function __construct()
    {
        $controllerMapper = fopen("./vendor/excalibur/AutoMapper/mappers/ControllerMapper", "w");
        $controllerFiles  = scandir("./src/Application/Controllers");
        unset($controllerFiles[0]);
        unset($controllerFiles[1]);
        $controllerFiles  = [...$controllerFiles];
        foreach ($controllerFiles as $controllerFileName) {
            $controllerFile = file_get_contents("./src/Application/Controllers/" . $controllerFileName);
            
        }
        fwrite($controllerMapper, $layoutContent);
    }
}
