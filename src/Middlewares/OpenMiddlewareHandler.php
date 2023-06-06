<?php

declare(strict_types=1);

namespace Excalibur\Framework\Middlewares;

use Excalibur\Framework\Middlewares\Interfaces\MiddlewareHandlerInterface;

class OpenMiddlewareHandler implements MiddlewareHandlerInterface
{
    public array $middlewares = [
        "auth" => \Excalibur\Framework\Middlewares\OpenAuthMiddleware::class,
    ];

    public function __construct(private $response)
    {}

    public function handle(?string $middleware): void
    {
        $found = [];
        foreach ($this->middlewares as $key => $middlewareItem) {
            if ($middlewareItem === $middleware || $key === $middleware) {
                $found[] = $middlewareItem;
            }
        }

        if (count($found) === 0) {
            throw new \LogicException("The $middleware middleware not found");
        }

        (new $found[0]($this->response))->handle();
    }
}
