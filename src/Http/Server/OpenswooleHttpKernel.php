<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server;

use Excalibur\Framework\Http\Interfaces\KernelInterface;
use Excalibur\Framework\Route\Router;
use Api\Requests\Request as ApiRequest;
use Api\Responses\Response as ApiResponse;
use WebUI\Requests\Request as WebRequest;
use WebUI\Responses\Response as WebResponse;
use Infrastructure\Helpers\View;
use Excalibur\Framework\Http\Server\Helpers\OpenBotChecker;
use Excalibur\Framework\Http\Server\Helpers\OpenSpaChecker;
use Excalibur\Framework\Http\Server\Helpers\OpenAssetChecker;
use Excalibur\Framework\Middlewares\OpenMiddlewareHandler;
use Intl\Translation;

class OpenswooleHttpKernel implements KernelInterface
{
    private OpenMiddlewareHandler $middlewareHandler;

    public function __construct(private \OpenSwoole\Http\Request $request, private \OpenSwoole\Http\Response $response)
    {
        $this->middlewareHandler = new OpenMiddlewareHandler($this->request, $this->response);
    }

    public function run()
    {
        $httpMethod = $this->getHttpMethod();

        $handle = $this->getRequestParamsAndRoutePath();
        $type   = $this->checkRouteType($handle->route);

        if (OpenAssetChecker::check($this->request->server["request_uri"])) {
            return $this->getDefaultFrontendFiles($handle->route, explode("/", $handle->route));
        }

        if ($type === "api") {
            return $this->processApiRequest(Router::searchRoute($httpMethod, $handle));
        }

        return $this->processWebRequest(Router::searchWebRoute($httpMethod, $handle));
    }

    private function processApiRequest($routeFound)
    {
            if ($routeFound->route === null) {
                $this->response->status(404);
                return "Error";
            }

            $request = (new ApiRequest());
            $response = (new ApiResponse($this->response));

            $input = $this->request->getContent();

            $request->params = (object) $routeFound->params;

            if ($input !== null || $input !== "") {
                $request->body = (object) json_decode($input);
            }

            if ($routeFound->route["controller"] === null) {
                return $this->response->end($routeFound->route["action"]($request, $response));
            }

            self::checkMiddleware($routeFound->route);

            return $this->response->end($routeFound->route["controller"]->{$routeFound->route["action"]}($request, $response));
    }

    private function processWebRequest($routeFound)
    {
        if ($routeFound->route === null) {
            $this->response->status(404);
            return $this->response->end("Page do not found");
        }

        $this->response->status(200);
        $this->response->header("Content-Type", "text/html");

        $request = (new WebRequest());
        $response = (new WebResponse());

        $input = $this->request->getContent();

        $isBot = OpenBotChecker::check($this->request->header["user-agent"]) ? "true" : "false";

        $langs = ["br", "pt", "fr", "de", "it", "en"];
        $l = "en";
        foreach ($langs as $lang) {    
            if (str_contains(strtolower($this->request->header["accept-language"]), "-".$lang)) {
                $l = $lang;
            }
        }

        $request->params = (object) [...((array) $routeFound->params), "isBot" => $isBot, "path" => $routeFound->route, "lang" => "$l", "intl" => Translation::$languages];

        if ($input !== null || $input !== "") {
            $request->body = (object) json_decode($input);
        }

        if ($routeFound->route["controller"] === null) {
            return $this->response->end($routeFound->route["action"]($request, $response));
        }

        self::checkMiddleware($routeFound->route);

        return $this->response->end($routeFound->route["controller"]->{$routeFound->route["action"]}($request, $response));
    }

    public function getDefaultFrontendFiles(string $path, array $pathArray)
    {
        $this->response->status(200);
        $this->response->header("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $this->response->header("Cache-Control", "post-check=0, pre-check=0", false);
        $this->response->header("Pragma", "no-cache");
        if (str_contains($path, "/scripts")) {
            return $this->getScript($pathArray);
        }

        if (str_contains($path, "/css")) {
            return $this->getCSS($pathArray);
        }

        if (str_contains($path, "/favicon")) {
            return $this->getFavicon();
        }

    }


    private function getScript(array $pathArray)
    {
        $fullPath = implode("/", $pathArray);
        $pathFormatted = explode("/scripts", $fullPath)[1];
        $this->response->header("Content-Type", "application/javascript");
        return $this->response->sendFile("./src/WebUI/Assets/Scripts/Javascript".$pathFormatted);
    }


    private function getCSS(array $pathArray)
    {
        $this->response->header("Content-Type", "text/css");
        return $this->response->sendFile("./src/WebUI/Assets/Styles/CSS/".$pathArray[count($pathArray) - 1]);
    }


    private function getFavicon()
    {
        $this->response->header("Content-Type", "image/x-icon");
        $this->response->header("Content-Disposition", "attachment; filename=\"favicon.icon\"");
        return $this->response->sendFile("./src/WebUI/Assets/Icons/favicon.ico");
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
        $route = Router::checkIfHasFinalDash($this->request->server["request_uri"]);

        $queryString = [];
        if (isset($this->request->server["query_string"]) && strlen($this->request->server["query_string"]) > 0) {
            if (str_contains($this->request->server["query_string"], "&")) {
                foreach (explode("&", $this->request->server["query_string"]) as $query) {
                    $queryKey   = explode("=", $query)[0];
                    $queryValue = explode("=", $query)[1];
                    $queryString["$queryKey"] = $queryValue;
                };
            } else {
                $query = $this->request->server["query_string"];
                $queryKey   = explode("=", $query)[0];
                $queryValue = explode("=", $query)[1];
                $queryString["$queryKey"] = $queryValue;
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
            "params" => [...$queryString, "token" => isset($this->request->header["authorization"])
                            ? str_replace("Bearer ", "", $this->request->header["authorization"]): null],
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
