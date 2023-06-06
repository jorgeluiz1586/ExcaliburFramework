<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server;

use Excalibur\Framework\Http\Interfaces\KernelInterface;
use Excalibur\Framework\Route\Router;
use Application\Http\Message\Request\Request;
use Application\Http\Message\Response\Response;
use Infrastructure\Helpers\View;
use Excalibur\Framework\Http\Server\Helpers\OpenBotChecker;
use Excalibur\Framework\Http\Server\Helpers\OpenSpaChecker;
use Excalibur\Framework\Http\Server\Helpers\OpenAssetChecker;
use Excalibur\Framework\Middlewares\OpenMiddlewareHandler;
use Excalibur\Framework\Http\Server\Header;

class OpenswooleHttpKernel implements KernelInterface
{
    private MiddlewareHandler $middlewareHandler;

    public function __construct(private \OpenSwoole\Http\Request $request, private \OpenSwoole\Http\Response $response)
    {
        $this->middlewareHandler = new OpenMiddlewareHandler($response);
        Header::setHeaders($this->request->header);
    }

    public function run()
    {
        $httpMethod = $this->getHttpMethod();

        $handle = $this->getRequestParamsAndRoutePath();
        $type   = $this->checkRouteType($handle->route);

        if (OpenAssetChecker::check($this->request->server["request_uri"])) {
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
                header("HTTP/1.1 404 Not Found");
                return "Error";
            }

            $request = (new Request());
            $response = (new Response());

            $input = file_get_contents("php://input");

            if ($input !== null || $input !== "") {
                $request->body = (object) json_decode($input);
            }

            if ($routeFound->route["controller"] === null) {
                return $this->response->end($routeFound->route["action"]($request, $response));
            }

            $request->params = (object) $routeFound->params;

            self::checkMiddleware($routeFound->route);

            return $this->response->end($routeFound->route["controller"]->{$routeFound->route["action"]}($request, $response));
    }

    private function processWebRequest($routeFound)
    {
        if ($routeFound->route === null) {
            header("HTTP/1.1 404 Not Found");
            return $this->response->end("Page do not found");
        } else {
            if (OpenBotChecker::check($this->request->header["user-agent"]) || !OpenSpaChecker::check($this->request->server["request_uri"])) {
                header("HTTP/1.1 200 OK");
               
                header("Content-Type: text/html");
                $result = [];
                View::setView([...$routeFound->route]["view"]);
                View::$isBot = OpenBotChecker::check($this->request->header["user-agent"]) ? "true" : "false";
                View::$params = (object) $routeFound->params;
                $result = View::render();
                return $this->response->end(implode("", $result));
            }
            header("HTTP/1.1 200 OK");
            header("Content-Type: text/html");
            $pages = [];
            foreach (Router::getWebRoutes() as $item) {
                View::setView(explode("/", $item["view"])[1]);
                View::$isBot = OpenBotChecker::check($this->request->header["user-agent"]) ? "true" : "false";

                View::$params = (object) $routeFound->params;
                $result = View::render();
                $pages[] = [
                    "path" => $item["uri"],
                    "page" => explode("<!---->", explode("<div id=\"app\">", implode("", $result))[1])[0],
                ];
            }
            return $this->response->end(json_encode($pages));
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


    private function getScript(array $pathArray)
    {
        header("Content-Type: application/javascript");
        return $this->response->end(file_get_contents("./src/WebUI/Assets/Scripts/".$pathArray[count($pathArray) - 1]));
    }


    private function getCSS(array $pathArray)
    {
        header("Content-Type: text/css");
        return $this->response->end(file_get_contents("./src/WebUI/Assets/Styles/CSS/".$pathArray[count($pathArray) - 1]));
    }


    private function getFavicon()
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
        $route = $this->request->server["request_uri"];

        $queryString = [];
        if (str_contains($this->request->server["request_uri"], "?")) {
            if (str_contains($this->request->server["request_uri"], "&")) {
                foreach (explode("&", explode("?", $this->request->server["request_uri"])[1]) as $query) {
                    $queryKey   = explode("=", $query)[0];
                    $queryValue = explode("=", $query)[1];
                    $queryString["$queryKey"] = $queryValue;
                };
                $route = explode("?", $this->request->server["request_uri"])[0];
            } else {
                $query = explode("?", $this->request->server["request_uri"])[1];
                $queryKey   = explode("=", $query)[0];
                $queryValue = explode("=", $query)[1];
                $queryString["$queryKey"] = $queryValue;
                $route = explode("?", $this->request->server["request_uri"])[0];
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
            "params" => [...$queryString, "token" => isset($this->request["authorization"])
                            ? str_replace("Bearer ", "", $this->request["authorization"]): null],
        ];
    }

    private function getHttpMethod(): string
    {
        return $this->request->server["request_method"];
    }

    private function checkMiddleware(array $route)
    {
        if (isset($route["middleware"]) && strlen($route["middleware"]) > 0) {
            $this->middlewareHandler->handle($route["middleware"]);
        }
    }
}
