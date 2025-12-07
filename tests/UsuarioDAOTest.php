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
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'username' => 'usuario_valido',
            'correo' => 'juan@gmail.com',
            'telefono' => '987654321',
            'fechaNacimiento' => '2000-05-12',
            'direccion' => 'Av. Siempre Viva 123',
            'password' => '123456',
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