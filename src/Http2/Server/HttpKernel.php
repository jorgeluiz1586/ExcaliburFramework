<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http2\Server;

use Excalibur\Framework\Http\Interfaces\KernelInterface;
use Excalibur\Framework\Route\Router;
use Application\Http\Message\Request\Request;
use Application\Http\Message\Response\Response;
use Infrastructure\Helpers\View;
use Excalibur\Framework\Http2\Server\Helpers\BotChecker;
use Excalibur\Framework\Http2\Server\Helpers\SpaChecker;
use Excalibur\Framework\Http2\Server\Helpers\AssetChecker;
use Excalibur\Framework\Middlewares\MiddlewareHandler;

class HttpKernel implements KernelInterface
{
    private MiddlewareHandler $middlewareHandler;

    public function __construct()
    {
        $this->middlewareHandler = new MiddlewareHandler();
    }

    public function run()
    {
        $httpMethod = $this->getHttpMethod();

        $handle = $this->getRequestParamsAndRoutePath();
        $type   = $this->checkRouteType($handle->route);

        if (AssetChecker::check()) {
            return self::getDefaultFrontendFiles($handle->route, explode("/", $handle->route));
        }

        if ($type === "api") {
            return $this->processApiRequest(Router::searchRoute($httpMethod, $handle));
        }

        return $this->processWebRequest(Router::searchWebRoute($httpMethod, $handle));
    }

    private function processApiRequest($routeFound)
    {
            if ($routeFound->route === null) {
                header("HTTP/2 404 Not Found");
                return "Error";
            }

            $request = (new Request());
            $response = (new Response());

            $input = file_get_contents("php://input");

            if ($input !== null || $input !== "") {
                $request->body = (object) json_decode($input);
            }

            if ($routeFound->route["controller"] === null) {
                return print_r($routeFound->route["action"]($request, $response));
            }

            $request->params = (object) $routeFound->params;

            self::checkMiddleware($routeFound->route);

            return print_r($routeFound->route["controller"]->{$routeFound->route["action"]}($request, $response));
    }

    private function processWebRequest($routeFound)
    {
        if ($routeFound->route === null) {
            header("HTTP/2 404 Not Found");
            return print_r("Page do not found");
        } else {
            if (BotChecker::check() || !SpaChecker::check()) {
                header("HTTP/2 200 OK");
               
                header("Content-Type: text/html");
                $result = [];
                View::setView([...$routeFound->route]["view"]);
                View::$isBot = BotChecker::check() ? "true" : "false";
                View::$params = (object) $routeFound->params;
                $result = View::render();
                return print_r(implode("", $result));
            }
            header("HTTP/2 200 OK");
            header("Content-Type: text/html");
            $pages = [];
            foreach (Router::getWebRoutes() as $item) {
                View::setView(explode("/", $item["view"])[1]);
                View::$isBot = BotChecker::check() ? "true" : "false";

                View::$params = (object) $routeFound->params;
                $result = View::render();
                $pages[] = [
                    "path" => $item["uri"],
                    "page" => explode("<!---->", explode("<div id=\"app\">", implode("", $result))[1])[0],
                ];
            }
            return print_r(json_encode($pages));
        }
    }

    public static function getDefaultFrontendFiles(string $path, array $pathArray)
    {
        header("HTTP/2 200 OK");
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
        $route = $_SERVER["REQUEST_URI"];

        $queryString = [];
        if (str_contains($_SERVER["REQUEST_URI"], "?")) {
            if (str_contains($_SERVER["REQUEST_URI"], "&")) {
                foreach (explode("&", explode("?", $_SERVER["REQUEST_URI"])[1]) as $query) {
                    $queryKey   = explode("=", $query)[0];
                    $queryValue = explode("=", $query)[1];
                    $queryString["$queryKey"] = $queryValue;
                };
                $route = explode("?", $_SERVER["REQUEST_URI"])[0];
            } else {
                $query = explode("?", $_SERVER["REQUEST_URI"])[1];
                $queryKey   = explode("=", $query)[0];
                $queryValue = explode("=", $query)[1];
                $queryString["$queryKey"] = $queryValue;
                $route = explode("?", $_SERVER["REQUEST_URI"])[0];
            }
        }

        $queryString["sessionId"] = session_id();
        if (isset($_SESSION["token"]) && (strlen($_SESSION["token"]) > 0)) {
            $queryString["token"] = $_SESSION["token"];
            $queryString["userId"] = $_SESSION["user"]->user_id;
            $queryString["userUuid"] = $_SESSION["user"]->user_uuid;
            $queryString["userName"] = $_SESSION["user"]->user_name;
            $queryString["userLastName"] = $_SESSION["user"]->user_last_name;
            $queryString["userEmail"] = $_SESSION["user"]->user_email;
        }

        return (object) [
            "route" => $route,
            "params" => [...$queryString, "token" => isset($_SERVER["HTTP_AUTHORIZATION"])
                            ? str_replace("Bearer ", "", $_SERVER["HTTP_AUTHORIZATION"]): null],
        ];
    }

    private function getHttpMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    private function checkMiddleware(array $route)
    {
        if (isset($route["middleware"]) && strlen($route["middleware"]) > 0) {
            $this->middlewareHandler->handle($route["middleware"]);
        }
    }
}
