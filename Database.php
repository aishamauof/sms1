<?php
class Database
{
    private $user;
    private $host;
    private $pass;
    private $db;
    private $conn;
    public function __construct()
    {
        $this->user = "root";
        $this->host = "localhost";
        $this->pass = "";
        $this->db = "sms";
    }

    public function getServerName()
    {
        return $this->host;
    }
    public function getUser()
    {
        return $this->user;
    }
    public function establishConnection()
    {

        $this->conn = @new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_error) {
            throw new Exception("<p>Error: Could not connect to database.<br/>
                Please try again later.</p>", 1);
        }
        return $this->conn;
    }
    public function closeConnection()
    {
        $this->conn->close();
        // echo "<br> Database is closed";
    }
}
?>