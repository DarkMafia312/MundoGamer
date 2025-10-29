<?php
require_once 'interfaces.php';

class Database implements Connectable {
    protected $host = "localhost";
    protected $user = "root";
    protected $pass = "";
    protected $dbname = "mundogamer_db";
    protected $port = 3307;
    protected $conn;

    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
        if ($this->conn->connect_error) {
            die("❌ Error de conexión: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        if (!$this->conn) $this->connect();
        return $this->conn;
    }
}

class ReadOnlyDatabase extends Database {
    public function getConnection() {
        parent::connect();
        $this->conn->query("SET SESSION TRANSACTION READ ONLY");
        return $this->conn;
    }
}
?>