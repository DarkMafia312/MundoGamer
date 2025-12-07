<?php
require_once 'database.php';
require_once 'interfaces.php';

class ProductoDAO implements Exportable {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $sql = "SELECT * FROM productos";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function add($data) {
        $sql = "INSERT INTO productos (titulo, genero, id_proveedor, descripcion, precio, plataforma, fecha_lanzamiento, rating_promedio, imagen, estado, vip)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // ⚠️ Se reemplazó la función de finalización abrupta por una excepción más controlada
            throw new Exception("❌ Error al preparar la consulta: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssisdssdssi",
            $data['titulo'],
            $data['genero'],
            $data['id_proveedor'],
            $data['descripcion'],
            $data['precio'],
            $data['plataforma'],
            $data['fecha_lanzamiento'],
            $data['rating_promedio'],
            $data['imagen'],
            $data['estado'],
            $data['vip']
        );

        $ok = $stmt->execute();
        if (!$ok) {
            // Se muestra el error pero sin interrumpir la ejecución
            echo "<p style='color:red;'>❌ Error al insertar producto: " . htmlspecialchars($stmt->error) . "</p>";
        }

        $stmt->close();
        return $ok;
    }

    // 🔹 Método usado por las pruebas funcionales
    public function obtenerProductoPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Devuelve el producto o null si no existe
    }

    public function exportToExcel($data, $filename) {
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
?>