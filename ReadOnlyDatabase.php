<?php
require_once __DIR__ . '/database.php';

class ReadOnlyDatabase extends Database {

    public function connect() {

        $this->conn = @new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->dbname,
            $this->port
        );

        if ($this->conn->connect_error) {
            throw new Exception("Error de conexión (solo lectura): " . $this->conn->connect_error);
        }

        // UTF-8 completo
        $this->conn->set_charset("utf8mb4");

        // Forzar sesión como solo lectura
        $this->conn->query("SET SESSION TRANSACTION READ ONLY");

        return $this->conn;
    }
}
?>
