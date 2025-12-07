<?php
require_once __DIR__ . '/security.php';

class Database {

    protected $host   = "localhost";
    protected $user   = "root";
    protected $pass   = "MiJuego2025!";
    protected $dbname = "mundogamer_db";
    protected $port   = 3307;

    protected $conn;

    // Conexión principal
    public function connect() {

        $this->conn = @new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->dbname,
            $this->port
        );

        if ($this->conn->connect_error) {
            throw new Exception("Conexión fallida: " . $this->conn->connect_error);
        }

        // UTF-8 completo
        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }

    // Obtener conexión activa
    public function getConnection() {
        if (!$this->conn) {
            return $this->connect();
        }
        return $this->conn;
    }
}
?>
