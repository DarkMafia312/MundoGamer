<?php
session_start();
include 'conexion.php';
require_once __DIR__ . '/fpdf/fpdf.php'; // Asegúrate que la carpeta fpdf exista y contenga fpdf.php

if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "⚠️ Acceso no autorizado";
    header("Location: usuario-login.php");
    exit();
}

$idVenta = $_GET['id_venta'] ?? 0; // 🔹 Corregido aquí
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// 🔹 Verificar que la venta pertenece al usuario
$stmt = $conn->prepare("SELECT * FROM ventas WHERE id_venta = ? AND id_usuario = ?");
$stmt->bind_param("ii", $idVenta, $idUsuario);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) {
    echo "<h2 style='color:red; text-align:center;'>⚠️ Venta no encontrada o no pertenece al usuario.</h2>";
    exit();
}


// 🔹 Obtener información de pago
$stmtPago = $conn->prepare("SELECT * FROM pagos WHERE id_venta = ?");
$stmtPago->bind_param("i", $idVenta);
$stmtPago->execute(); 
$pago = $stmtPago->get_result()->fetch_assoc();

// 🔹 Obtener detalle de productos
$stmtDetalle = $conn->prepare("
    SELECT d.*, p.titulo, p.plataforma
    FROM detalle_venta d
    INNER JOIN productos p ON d.id_producto = p.id_producto
    WHERE d.id_venta = ?
");
$stmtDetalle->bind_param("i", $idVenta);
$stmtDetalle->execute();
$detalles = $stmtDetalle->get_result();

// 🔹 Datos del cliente
$usuario = $_SESSION['usuario']['nombre'] ?? "Usuario";

// =====================
// 🧾 GENERAR VOUCHER PDF
// =====================
class PDF extends FPDF {
    function Header() {
        if (file_exists('img/logo.png')) {
            $this->Image('img/logo.png', 10, 8, 25);
        }
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(100, 10, utf8_decode('Comprobante de Compra - MundoGamer'), 0, 0, 'C');
        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Gracias por tu compra en MundoGamer - ') . date('d/m/Y H:i:s'), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Información general
$pdf->Cell(0, 10, utf8_decode("Cliente: " . $usuario), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Fecha de compra: " . $venta['fecha_venta']), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Método de pago: " . $venta['metodo_pago']), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Código de transacción: " . ($pago['codigo_transaccion'] ?? '---')), 0, 1);
$pdf->Ln(5);

// Línea divisoria
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode("Detalle de productos"), 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(80, 8, "Producto", 1);
$pdf->Cell(40, 8, "Plataforma", 1);
$pdf->Cell(20, 8, "Cant.", 1);
$pdf->Cell(25, 8, "Precio (S/)", 1);
$pdf->Cell(25, 8, "Subtotal (S/)", 1);
$pdf->Ln();

// 🔹 Agregar filas de productos
$pdf->SetFont('Arial', '', 10);
while ($fila = $detalles->fetch_assoc()) {
    $pdf->Cell(80, 8, utf8_decode($fila['titulo']), 1);
    $pdf->Cell(40, 8, utf8_decode($fila['plataforma']), 1);
    $pdf->Cell(20, 8, $fila['cantidad'], 1, 0, 'C');
    $pdf->Cell(25, 8, number_format($fila['precio_unitario'], 2), 1, 0, 'R');
    $pdf->Cell(25, 8, number_format($fila['subtotal'], 2), 1, 0, 'R');
    $pdf->Ln();
}

// Totales
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140, 8, "Subtotal:", 0, 0, 'R');
$pdf->Cell(30, 8, "S/" . number_format($venta['total'], 2), 0, 1, 'R');

$pdf->Cell(140, 8, "Descuento:", 0, 0, 'R');
$pdf->Cell(30, 8, "-S/" . number_format($venta['descuento'], 2), 0, 1, 'R');

$pdf->Cell(140, 8, "Total Final:", 0, 0, 'R');
$pdf->Cell(30, 8, "S/" . number_format($venta['total_final'], 2), 0, 1, 'R');

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 8, utf8_decode("Este documento es un comprobante de compra digital generado automáticamente por el sistema MundoGamer. No requiere firma ni sello."));

$pdf->Output("I", "Voucher_Compra_MundoGamer_" . $idVenta . ".pdf");
exit;
?>