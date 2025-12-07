<?php
require_once 'database.php';
require_once 'interfaces.php';

class UsuarioDAO implements Exportable {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM usuarios";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function add($data) {
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nombre, apellido, username, correo, telefono, fechaNacimiento, direccion, password, estado, fechaRegistro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssss", $data['nombre'], $data['apellido'], $data['username'], $data['correo'], $data['telefono'], $data['fechaNacimiento'], $data['direccion'], $data['password'], $data['estado']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // 🔹 NUEVO: Método para login funcional
    public function verificarUsuario($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            // Verifica hash o texto plano
            if (password_verify($password, $usuario['password']) || $usuario['password'] === $password) {
                return $usuario; // Retorna los datos del usuario si es válido
            }
        }
        return false; // Usuario no encontrado o contraseña incorrecta
    }

    public function exportToExcel($data, $filename) {
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    }
}
?>