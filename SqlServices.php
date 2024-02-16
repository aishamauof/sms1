<?php

class SqlServices
{
    public $connection;
    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function insert_record($sql)
    {
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new Exception($this->connection->error);
        }
        return $result;
    }
    public function query($sql)
    {
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new Exception($this->connection->error);
        }
        return $result;
    }
}