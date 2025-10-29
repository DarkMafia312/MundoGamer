<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TrabajadorDAO.php';

class TrabajadorDAOTest extends TestCase {
    private $dao;

    protected function setUp(): void {
        $this->dao = new TrabajadorDAO();
    }

    public function testAddTrabajador(): void {
        $nuevo = [
            'nombres' => 'Lucía',
            'apellidos' => 'Quispe',
            'dni' => '70881234',
            'correo' => 'lucia@gmail.com',
            'fechaNacimiento' => '1992-08-15',
            'puesto' => 'Gerente',
            'fechaContratacion' => '2024-01-10',
            'sueldo' => 1800.00,
            'estado' => 'activo'
        ];

        $this->assertTrue(
            $this->dao->add($nuevo),
            '❌ No se pudo agregar el trabajador correctamente.'
        );
    }

    public function testGetAllTrabajadores(): void {
        $trabajadores = $this->dao->getAll();

        $this->assertIsArray(
            $trabajadores,
            '❌ La función getAll() no devolvió un array.'
        );
    }
}
?>