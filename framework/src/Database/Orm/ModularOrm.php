<?php

namespace Excalibur\Framework\Database\Orm;

use Excalibur\Framework\Database\Interfaces\DatabaseInterface;
use Excalibur\Framework\Database\Interfaces\DatabaseConnectionInterface;

class ExcaliburOrm
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
