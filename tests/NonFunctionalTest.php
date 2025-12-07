<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../database.php';

class NonFunctionalTest extends TestCase {

    public function testTiempoDeRespuestaBaseDeDatos() {
        $inicio = microtime(true);
        $db = new Database();
        $conn = $db->getConnection();
        $fin = microtime(true);

        $tiempo = $fin - $inicio;
        // No debería tardar más de 1 segundo
        $this->assertLessThan(1.0, $tiempo, "La conexión a la base de datos fue demasiado lenta.");
    }

    public function testCargaMasivaDeDatos() {
        $db = new Database();
        $conn = $db->getConnection();

        $inicio = microtime(true);
        $resultado = $conn->query("SELECT * FROM productos LIMIT 1000");
        $fin = microtime(true);

        $tiempo = $fin - $inicio;
        $this->assertLessThan(2.0, $tiempo, "La consulta masiva de productos fue demasiado lenta.");
        $this->assertIsObject($resultado, "La consulta no devolvió un resultado válido.");
    }

    public function testSeguridadContraInyeccionSQL() {
        $db = new Database();
        $conn = $db->getConnection();

        $entradaMaliciosa = "' OR '1'='1";
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $entradaMaliciosa);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $this->assertEmpty($resultado->fetch_all(), "Posible vulnerabilidad a inyección SQL detectada.");
    }

    public function testUsoDeMemoria() {
        $memInicio = memory_get_usage();
        $db = new Database();
        $conn = $db->getConnection();
        $memFin = memory_get_usage();

        $uso = $memFin - $memInicio;
        // No debería superar los 10MB en este proceso
        $this->assertLessThan(10 * 1024 * 1024, $uso, "El uso de memoria fue excesivo.");
    }
}