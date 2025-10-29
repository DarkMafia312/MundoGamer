<?php
require_once 'database.php';

class ReadOnlyDatabase extends Database {
    protected function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);

        if ($this->conn->connect_error) {
            die("❌ Error de conexión (solo lectura): " . $this->conn->connect_error);
        }

        // Solo lectura
        $this->conn->query("SET SESSION TRANSACTION READ ONLY");
    }
}
?>