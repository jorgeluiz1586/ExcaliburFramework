<?php

declare(strict_types=1);

namespace Excalibur\Framework\Middlewares;

use Excalibur\Framework\Middlewares\Interfaces\MiddlewareHandlerInterface;
use Infrastructure\Config\Database;

class AuthMiddleware implements MiddlewareHandlerInterface
{
    public function handle(?string $middleware = null): void
    {

        $token = isset($_SERVER["HTTP_AUTHORIZATION"])
        ? str_replace("Bearer ", "", $_SERVER["HTTP_AUTHORIZATION"]): null;
        if (gettype($token) === "string" && strlen($token) > 8) {
            $result = (object) Database::config()->query(
                "SELECT 
                    u.id as user_id,
                    u.uuid as user_uuid, 
                    u.first_name as user_name, 
                    u.last_name as user_last_name, 
                    u.email as user_email, 
                    u.phone as user_phone,
                    t.id as token_id, 
                    t.uuid as token_uuid, 
                    t.token as token,
                    t.deadline as token_deadline,
                    t.created_at as token_created_at,
                    t.updated_at as token_updated_at
                FROM tokens as t 
                JOIN users as u ON u.id = t.user_id
                where t.token = '".$token."';")
                ->fetchObject();

            if (!(isset($result->user_id) && isset($result->token_uuid))) {
                header("HTTP/1.1 401 Unauthorized");
                print_r("Token Invalid");
                die();
            }
            $_SESSION["token"] = $result->token;
            $_SESSION["user"] = $result;
        } else {
            header("HTTP/1.1 401 Unauthorized");
            print_r("Unauthorized");
            die();
        }
    }
}
