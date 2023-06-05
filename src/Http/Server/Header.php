<?php

namespace Excalibur\Framework\Http\Server;

class Header
{
    private $headers = [];

    public static function getHeaders()
    {
        return self::$headers;
    }

    public static function setHeaders(array $headers)
    {
        self::$headers = $headers;
    }
}
