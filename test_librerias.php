<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\String\UnicodeString;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$results = [];

// === TEST SYMFONY STRING ===
try {
    $string = new UnicodeString('mundo gamer');
    $results[] = [
        'name' => 'Symfony String',
        'status' => 'success',
        'message' => 'Resultado: ' . $string->upper()
    ];
} catch (Throwable $e) {
    $results[] = [
        'name' => 'Symfony String',
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// === TEST PHPSPREADSHEET ===
try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hola MundoGamer!');
    $writer = new Xlsx($spreadsheet);
    $writer->save(__DIR__ . '/test_excel.xlsx');
    $results[] = [
        'name' => 'PhpSpreadsheet',
        'status' => 'success',
        'message' => 'Archivo generado correctamente en test_excel.xlsx'
    ];
} catch (Throwable $e) {
    $results[] = [
        'name' => 'PhpSpreadsheet',
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// === TEST MONOLOG ===
try {
    $log = new Logger('MundoGamer');
    $log->pushHandler(new StreamHandler(__DIR__ . '/test.log', Logger::INFO));
    $log->info('✅ Monolog funcionando correctamente');
    $results[] = [
        'name' => 'Monolog',
        'status' => 'success',
        'message' => 'Log creado correctamente en test.log'
    ];
} catch (Throwable $e) {
    $results[] = [
        'name' => 'Monolog',
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MundoGamer | Test de Librerías</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: radial-gradient(circle at center, #0a0a0a, #000);
        color: #fff;
        text-align: center;
        overflow: hidden;
        min-height: 100vh;
    }

    h1 {
        color: #00ffcc;
        margin-top: 50px;
        font-size: 2.5rem;
        text-shadow: 0 0 20px #00ffcc;
        animation: glow 2s infinite alternate;
    }

    @keyframes glow {
        from { text-shadow: 0 0 10px #00ffcc; }
        to { text-shadow: 0 0 25px #00ffcc, 0 0 50px #1db954; }
    }

    .container {
        margin: 60px auto;
        width: 80%;
        max-width: 700px;
    }

    .card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        text-align: left;
        box-shadow: 0 0 15px rgba(0,255,204,0.2);
        position: relative;
        animation: fadeUp 0.8s ease forwards;
        opacity: 0;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card.success {
        border-left: 6px solid #00ffcc;
    }

    .card.error {
        border-left: 6px solid #ff4d4d;
    }

    .status {
        font-weight: bold;
        font-size: 1.1rem;
    }

    .status.success { color: #00ffcc; }
    .status.error { color: #ff4d4d; }

    footer {
        color: #777;
        font-size: 0.9rem;
        margin-top: 40px;
    }

    /* Animación de partículas */
    canvas {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

</style>
</head>
<body>

<canvas id="bgCanvas"></canvas>

<h1>✅ Test de Librerías — MundoGamer</h1>

<div class="container">
    <?php foreach ($results as $index => $res): ?>
        <div class="card <?= $res['status'] ?>" style="animation-delay: <?= $index * 0.3 ?>s">
            <h2><?= htmlspecialchars($res['name']) ?></h2>
            <p class="status <?= $res['status'] ?>">
                <?= $res['status'] === 'success' ? '✔ Éxito' : '❌ Error' ?>
            </p>
            <p><?= htmlspecialchars($res['message']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<footer>© <?= date('Y') ?> MundoGamer — Infinity Beyond Play ⚡</footer>

<script>
// Partículas animadas en fondo
const canvas = document.getElementById('bgCanvas');
const ctx = canvas.getContext('2d');
let w, h, particles;
function resize() {
    w = canvas.width = window.innerWidth;
    h = canvas.height = window.innerHeight;
    particles = Array.from({length: 80}, () => ({
        x: Math.random() * w,
        y: Math.random() * h,
        r: Math.random() * 2,
        dx: (Math.random() - 0.5) * 0.7,
        dy: (Math.random() - 0.5) * 0.7
    }));
}
function draw() {
    ctx.clearRect(0, 0, w, h);
    ctx.fillStyle = '#00ffcc';
    particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fill();
        p.x += p.dx;
        p.y += p.dy;
        if (p.x < 0 || p.x > w) p.dx *= -1;
        if (p.y < 0 || p.y > h) p.dy *= -1;
    });
    requestAnimationFrame(draw);
}
resize();
draw();
window.addEventListener('resize', resize);
</script>

</body>
</html>