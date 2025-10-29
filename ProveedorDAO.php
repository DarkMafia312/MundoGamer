<?php
require_once 'database.php';
require_once 'interfaces.php';

class ProveedorDAO implements Exportable {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM proveedores";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function add($data) {
        $stmt = $this->conn->prepare("INSERT INTO proveedores (empresa, ruc, telefono, correo, direccion, paga, fechaRegistro)
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $data['empresa'], $data['ruc'], $data['telefono'], $data['correo'], $data['direccion'], $data['paga']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM proveedores WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function exportToExcel($data, $filename) {
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    }
}
?>