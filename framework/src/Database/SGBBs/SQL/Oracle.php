<?php

declare(strict_types=1);

namespace Modular\Framework\Database\SQL;

use Modular\Framework\Database\Interfaces\DatabaseInterface;
use Modular\Framework\Database\Interfaces\DatabaseConnectionInterface;

class Oracle implements DatabaseInterface
{
    private DatabaseConnectionInterface $connection;

    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }

    public function setConnection(object $connection): void
    {
        $dns = $connection->connection.
                    ":host=".$connection->host.
                    ";port=".$connection->port.
                    ";dbname=".$connection->database.
                    ";user=".$connection->username.
                    ";password=".$connection->password;

        $this->connection = (new \PDO(
            $dns
        ));
    }
}
