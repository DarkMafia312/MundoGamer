<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../ProveedorDAO.php';

class ProveedorDAOTest extends TestCase {
    private $dao;

    protected function setUp(): void {
        $this->dao = new ProveedorDAO();
    }

    public function testAddProveedor(): void {
        $nuevo = [
            'empresa' => 'Trujillo Gameplay',
            'ruc' => '20457896543',
            'telefono' => '987472650',
            'correo' => 'ica@gmail.com',
            'direccion' => 'Av. Prado 450',
            'paga' => '7000'
        ];

        $this->assertTrue(
            $this->dao->add($nuevo),
            '❌ No se pudo agregar el proveedor.'
        );
    }

    public function testGetAllProveedores(): void {
        $proveedores = $this->dao->getAll();
        $this->assertIsArray(
            $proveedores,
            '❌ La función getAll() no devolvió un array.'
        );
    }
}
?>