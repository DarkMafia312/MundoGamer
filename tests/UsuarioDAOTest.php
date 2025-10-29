<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../UsuarioDAO.php';

class UsuarioDAOTest extends TestCase {
    private $dao;

    protected function setUp(): void {
        $this->dao = new UsuarioDAO();
    }

    public function testAddUsuario(): void {
        $nuevo = [
            'nombre' => 'Maria Juana',
            'apellido' => 'Gonzales Casas',
            'username' => 'Gonza013',
            'correo' => 'marigoca@gmail.com',
            'telefono' => '921852633',
            'fechaNacimiento' => '2000-01-01',
            'direccion' => 'Av. Trujillo 123',
            'password' => '1234',
            'estado' => 'activo'
        ];

        $this->assertTrue(
            $this->dao->add($nuevo),
            '❌ No se pudo agregar el usuario.'
        );
    }

    public function testGetAllUsuarios(): void {
        $usuarios = $this->dao->getAll();
        $this->assertIsArray(
            $usuarios,
            '❌ La función getAll() no devolvió un array.'
        );
    }
}
?>