<?php
$host = getenv('DB_HOST') ?: 'db';
$base = 'fogon';
$usuario = 'fogon';
$clave = 'fogon123';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$base;charset=utf8mb4",
        $usuario,
        $clave,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Error de conexión</title>';
    echo '<link rel="stylesheet" href="/alquiler/css/estilos.css"></head><body><main class="contenedor">';
    echo '<div class="alerta-error"><h2>La despensa no responde</h2>';
    echo '<p>No se pudo conectar con MySQL. Verificá que el contenedor <code>fogon-db</code> esté encendido y saludable (healthy).</p>';
    echo '<p class="detalle-tecnico">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</div></main></body></html>';
    exit;
}
