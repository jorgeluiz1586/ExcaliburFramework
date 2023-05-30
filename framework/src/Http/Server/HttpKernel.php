<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server;

use Excalibur\Framework\Http\Interfaces\KernelInterface;
use Excalibur\Framework\Route\Router;

class HttpKernel implements KernelInterface
{
    public function run()
    {
        $isSPA      = $this->isSPA();
        $isBot      = $this->isBot();
        $httpMethod = $this->getHttpMethod();

        $handle = $this->getRequestParamsAndRoutePath();
        $type   = $this->checkRouteType($handle->route);
        $isAsset  = $this->hasAsset();

        if ($isAsset) {
            return Router::getDefaultFrontendFiles($handle->route, explode("/", $handle->route));
        }

        if ($type === "api") {
            return Router::searchRoute($httpMethod, $handle);
        }
        return Router::searchWebRoute($httpMethod, $handle, ((object) ["isBot" => $isBot, "isSPA" => $isSPA]));
    }

    private function checkRouteType(string $path)
    {
        if (str_contains($path, "/api")) {
            return "api";
        }

        return "web";
    }

    private function getRequestParamsAndRoutePath()
    {
        $route = $_SERVER['REQUEST_URI'];

        $queryString = [];
        if (str_contains($_SERVER['REQUEST_URI'], "?")) {
            if (str_contains($_SERVER['REQUEST_URI'], "&")) {
                foreach (explode("&", explode("?", $_SERVER['REQUEST_URI'])[1]) as $query) {
                    $queryKey   = explode("=", $query)[0];
                    $queryValue = explode("=", $query)[1];
                    $queryString["$queryKey"] = $queryValue;
                };
                $route = explode("?", $_SERVER['REQUEST_URI'])[0];
            } else {
                $query = explode("?", $_SERVER['REQUEST_URI'])[1];
                $queryKey   = explode("=", $query)[0];
                $queryValue = explode("=", $query)[1];
                $queryString["$queryKey"] = $queryValue;
                $route = explode("?", $_SERVER['REQUEST_URI'])[0];
            }
        }

        return (object) [
            "route" => $route,
            "params" => $queryString,
        ];
    }

    private function isBot(): bool
    {
        return
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "bot") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "google") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "brave") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "duckduckgo") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "bing") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "yandex") ||
            str_contains(strtolower($_SERVER['HTTP_USER_AGENT']), "yahoo");
    }

    private function isSPA(): bool
    {
        return str_contains(strtolower($_SERVER['REQUEST_URI']), "spa=true");
    }

    private function getHttpMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private function hasAsset(): bool
    {
        return
            str_contains($_SERVER['REQUEST_URI'], "/scripts") ||
            str_contains($_SERVER['REQUEST_URI'], "/css") ||
            str_contains($_SERVER['REQUEST_URI'], "/favicon");
    }
}
