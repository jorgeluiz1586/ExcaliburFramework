<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server;

use Excalibur\Framework\Http\Interfaces\KernelInterface;
use Excalibur\Framework\Route\Router;
use Application\Http\Message\Request\Request;
use Application\Http\Message\Response\Response;
use Excalibur\Framework\Http\Server\Helpers\BotChecker;
use Infrastructure\Config\Database;
use Infrastructure\Helpers\View;
use Excalibur\Framework\Http\Server\Helpers\SpaChecker;

class HttpKernel implements KernelInterface
{
    public function run()
    {
        $httpMethod = $this->getHttpMethod();

        $handle = $this->getRequestParamsAndRoutePath();
        $type   = $this->checkRouteType($handle->route);
        $isAsset  = $this->hasAsset();

        if ($isAsset) {
            return self::getDefaultFrontendFiles($handle->route, explode("/", $handle->route));
        }

        if ($type === "api") {
            return $this->processApiRequest(Router::searchRoute($httpMethod, $handle));
        }

        return $this->processWebRequest(
            Router::searchWebRoute($httpMethod, $handle));
    }

    private function processApiRequest($routeFound)
    {
            if (empty($routeFound->route)) {
                header("HTTP/1.1 404 Not Found");
                return "Error";
            }

            $request = (new Request());
            $response = (new Response());

            $input = file_get_contents("php://input");

            if ($input !== null || $input !== "") {
                $request->body = (object) json_decode($input);
            }

            if ($routeFound->route['controller'] === null) {
                return print_r($routeFound->route['action']($request, $response));
            }

            $request->params = (object) $routeFound->params;

            self::checkMiddleware($request, $routeFound->route);

            return print_r($routeFound->route['controller']->{$routeFound->route['action']}($request, $response));
    }

    private function processWebRequest($routeFound)
    {
        if (count($routeFound->route) <= 0) {
            header("HTTP/1.1 404 Not Found");
            return print_r("Page do not found");
        } else {
            if (BotChecker::check() || !SpaChecker::check()) {
                header("HTTP/1.1 200 OK");
               
                header("Content-Type: text/html");
                $result = [];
                View::setView([...$routeFound->route][0]['view']);
                View::$isBot = BotChecker::check() ? "true" : "false";
                View::$params = (object) $routeFound->params;
                $result = View::render();
                return print_r(implode("", $result));
            }
            header("HTTP/1.1 200 OK");
            header("Content-Type: text/html");
            $pages = [];
            foreach (self::$webRoutes as $item) {
                View::setView(explode("/", $item['view'])[1]);
                View::$isBot = BotChecker::check() ? "true" : "false";

                View::$params = (object) $routeFound->params;
                $result = View::render();
                $pages[] = [
                    "path" => $item['uri'],
                    "page" => explode("<!---->", explode("<div id=\"app\">", implode("", $result))[1])[0],
                ];
            }
            return print_r(json_encode($pages));
        }
    }

    public static function getDefaultFrontendFiles(string $path, array $pathArray)
    {
        header("HTTP/1.1 200 OK");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        if (str_contains($path, "/scripts")) {
            return self::getScript($pathArray);
        }

        if (str_contains($path, "/css")) {
            return self::getCSS($pathArray);
        }

        if (str_contains($path, "/favicon")) {
            return self::getFavicon();
        }

    }


    private static function getScript(array $pathArray)
    {
        header("Content-Type: application/javascript");
        return print_r(file_get_contents("./src/WebUI/Assets/Scripts/".$pathArray[count($pathArray) - 1]));
    }


    private static function getCSS(array $pathArray)
    {
        header("Content-Type: text/css");
        return print_r(file_get_contents("./src/WebUI/Assets/Styles/CSS/".$pathArray[count($pathArray) - 1]));
    }


    private static function getFavicon()
    {
        header("Content-Type: image/x-icon");
        header("Content-Disposition:attachment; filename=\"favicon.icon\"");
        return readfile("./src/WebUI/Assets/Icons/favicon.ico");
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

    private static function checkMiddleware(object $request, array $route)
    {
        if (isset($route['middleware']) && strlen($route['middleware']) > 0) {
            $token = $request->getToken();
            if (gettype($token) === "string" && strlen($token) > 8) {
                $result = (object) Database::config()->query(
                    "SELECT * FROM tokens where token = '".$token."';")->fetchObject();
            } else {
                header("HTTP/1.1 401 Unauthorized");
                print_r("Error! Unauthorized");
                die();
            }
        }
    }


    private static function injectDependencies(array $dependencyPathArray, string $path, string $fileType)
    {
        $applicationPathArray    = $dependencyPathArray;
        $applicationPathArray[0] = "$path";
        unset($applicationPathArray[1]);
        $applicationPathArray[2] = str_replace("Controller", "$fileType", $dependencyPathArray[2]);
        return implode("\\", $applicationPathArray);
    }

}
