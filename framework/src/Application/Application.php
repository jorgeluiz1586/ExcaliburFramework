<?php

declare(strict_types=1);

namespace Excalibur\Framework;

use Excalibur\Framework\Http\Interfaces\KernelInterface;

class Application
{
    private array $providers = [];
    private array $config    = [];
    private array $alias     = [];

    public function __construct(private KernelInterface $kernel)
    {}

    public function setProvider(object $provider)
    {
        $this->providers[] = $provider;
    }

    public function setConfiguration(object $config)
    {
        $this->config[$config->key] = $config->value;
    }

    public function setAlias(object $alias)
    {
        $this->alias[$alias->key] = $alias->value;
    }

    public function start()
    {
        $this->sendResponse($this->kernel->run());
    }

    public function sendResponse(object $handle)
    {
        if (isset($_SESSION["token"]) && (strlen($_SESSION["token"]) > 0)) {
            $handle->params['token'] = $_SESSION["token"];
            $handle->params['userId'] = $_SESSION["user"]->id;
            $handle->params['userUuid'] = $_SESSION["user"]->uuid;
            $handle->params['userName'] = $_SESSION["user"]->first_name;
            $handle->params['userLastName'] = $_SESSION["user"]->last_name;
            $handle->params['userEmail'] = $_SESSION["user"]->email;
        }
        if (empty($handle->route)) {
            //header("HTTP/1.1 404 Not Found");
            return "Error";
        }
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

        $request->params = (object) $handle->params;
    }
}
