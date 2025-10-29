<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../ProductoDAO.php';

class ProductoDAOTest extends TestCase {
    private $dao;

    protected function setUp(): void {
        $this->dao = new ProductoDAO();
    }

    public function testAddProducto(): void {
        $nuevo = [
            'titulo' => 'Cyberpunk 2077',
            'genero' => 'Acción',
            'id_proveedor' => 1,
            'descripcion' => 'Juego de rol de acción',
            'precio' => 199.90,
            'plataforma' => 'PC',
            'fecha_lanzamiento' => '2022-02-25',
            'rating_promedio' => 4.9,
            'imagen' => 'https://image.api.playstation.com/vulcan/ap/rnd/202311/2812/ae84720b553c4ce943e9c342621b60f198beda0dbf533e21.jpg',
            'estado' => 1,
            'vip' => 0
        ];

        $this->assertTrue(
            $this->dao->add($nuevo),
            '❌ Error: No se pudo agregar el producto correctamente.'
        );
    }

    public function testGetAllProductos(): void {
        $productos = $this->dao->getAll();

        $this->assertIsArray(
            $productos,
            '❌ Error: La función getAll() no devolvió un array.'
        );
    }
}
?>