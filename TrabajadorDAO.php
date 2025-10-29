<?php
require_once 'database.php';
require_once 'interfaces.php';

class TrabajadorDAO implements Exportable {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM trabajadores";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function add($data) {
        $stmt = $this->conn->prepare("INSERT INTO trabajadores (nombres, apellidos, dni, correo, fechaNacimiento, puesto, fechaContratacion, sueldo, estado, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssssdis", $data['nombres'], $data['apellidos'], $data['dni'], $data['correo'], $data['fechaNacimiento'], $data['puesto'], $data['fechaContratacion'], $data['sueldo'], $data['estado']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM trabajadores WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function exportToExcel($data, $filename) {
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    }
}
?>