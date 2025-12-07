<?php
use PHPUnit\Framework\TestCase;

class StaticAnalysisTest extends TestCase {

    public function testSintaxisPHPValida() {
        $archivos = glob(__DIR__ . '/../*.php');
        foreach ($archivos as $archivo) {
            $salida = null;
            $resultado = null;
            exec("php -l " . escapeshellarg($archivo), $salida, $resultado);
            $this->assertEquals(
                0,
                $resultado,
                "Error de sintaxis en: $archivo\n" . implode("\n", $salida)
            );
        }
    }

    public function testConvencionesDeNombresDeClases() {
        $archivos = glob(__DIR__ . '/../*DAO.php');
        foreach ($archivos as $archivo) {
            $contenido = file_get_contents($archivo);
            preg_match_all('/class\s+([A-Z][A-Za-z0-9]*)/', $contenido, $matches);

            foreach ($matches[1] as $nombreClase) {
                $this->assertTrue(
                    str_ends_with($nombreClase, 'DAO') || 
                    in_array($nombreClase, ['Database', 'ReadOnlyDatabase']),
                    "El nombre de la clase '$nombreClase' no sigue la convención (debe terminar en DAO o ser Database)."
                );
            }
        }
    }

    public function testArchivosSinCodigoMuerto() {
        $archivos = glob(__DIR__ . '/../*.php');
        foreach ($archivos as $archivo) {
            $contenido = file_get_contents($archivo);

            // 🧽 Limpiar comentarios para evitar falsos positivos
            $contenidoSinComentarios = preg_replace([
                '/\/\/.*$/m',                 // Comentarios de línea //
                '/\/\*[\s\S]*?\*\//'          // Comentarios multilínea /* ... */
            ], '', $contenido);

            $this->assertStringNotContainsString('var_dump', $contenidoSinComentarios, "El archivo $archivo contiene código de depuración (var_dump).");
            $this->assertStringNotContainsString('print_r', $contenidoSinComentarios, "El archivo $archivo contiene código de depuración (print_r).");
            $this->assertStringNotContainsString('die(', $contenidoSinComentarios, "El archivo $archivo usa 'die()', debería evitarse en producción.");
        }
    }

    public function testFormatoDeIndentacionBasico() {
        $archivos = glob(__DIR__ . '/../*.php');
        foreach ($archivos as $archivo) {
            $contenido = file_get_contents($archivo);
            $lineas = explode("\n", $contenido);
            foreach ($lineas as $i => $linea) {
                $this->assertFalse(
                    str_starts_with($linea, "\t"),
                    "El archivo $archivo usa tabulaciones en la línea " . ($i+1) . ". Usa espacios en su lugar."
                );
            }
        }
    }
}