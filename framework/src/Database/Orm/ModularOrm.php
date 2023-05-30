<?php

namespace Modular\Framework\Database\Orm;

use Modular\Framework\Database\Interfaces\DatabaseInterface;
use Modular\Framework\Database\Interfaces\DatabaseConnectionInterface;

class ModularOrm
{
    private DatabaseConnectionInterface $connection;

    private object $enitty;

    public function __construct(DatabaseInterface $db)
    {
        $this->connection = $db->getConnection();
    }

    public function getAll()
    {}

    public function getOneByID()
    {}

    public function filter()
    {}

    public function store()
    {}

    public function update()
    {}

    public function delete()
    {}
}
