<?php

declare(strict_types=1);

class ControllerInjector
{
    public $;
    public function __construct()
    {
        $request = (new Request());
        $response = (new Response());
        $input = file_get_contents("php://input");
        if ($input !== null || $input !== "") {
            $request->body = (object) json_decode($input);
        }
        if ($handle->route[0]['controller'] === null) {
            return print_r($handle->route['action']($request, $response));
        }

        $serviceClass    = self::injectDependencies(explode("\\", $handle->route['controller']),
                                "Application\\Services", "Service");
        $repositoryClass = self::injectDependencies(explode("\\", $handle->route['controller']),
                                "Infrastructure\\Data\\Repositories", "Repository");
        $entityClass     = self::injectDependencies(explode("\\", $handle->route['controller']),
                                "Domain\\Entities", "");
        $service         = new $serviceClass(new $repositoryClass(new $entityClass()));
    }
}
