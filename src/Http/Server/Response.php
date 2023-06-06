<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server;

use OpenSwoole\Http\Response;

class Response
{
    public static $response;

    public static function end(string $response)
    {
        if ($response !== null) {
            self::$response->end($response);
        }
    }

    public static function setResponse(Response $response)
    {
        self::$response = $response;
    }
}
