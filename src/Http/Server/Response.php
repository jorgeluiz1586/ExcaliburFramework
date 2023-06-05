<?php

declare(strict_types=1);

namespace Excalibur\Framework\Http\Server;

class Response
{
    private static $response;

    public static function end(string $response)
    {
        self::$response->end($response);
    }

    public static function setResponse(\OpenSwoole\Http\Response $response)
    {
        self::$response = $response;
    }
}
