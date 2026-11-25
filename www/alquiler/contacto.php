<?php
require __DIR__ . '/conexion.php';

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function entrada($clave) {
    $valor = $_POST[$clave] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

$modelos = [
    'Suzuki Swift',
    'Toyota Corolla',
    'Toyota RAV4',
    'Toyota Hilux',
];

$autoInicial = $_GET['auto'] ?? '';
$autoInicial = is_string($autoInicial) ? $autoInicial : '';

$datos = [
    'nombre' => '',
    'correo' => '',
    'auto_interes' => $autoInicial,
    'mensaje' => '',
];

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = entrada('nombre');
    $datos['correo'] = entrada('correo');
    $datos['auto_interes'] = entrada('auto_interes');
    $datos['mensaje'] = entrada('mensaje');

    if (mb_strlen($datos['nombre'], 'UTF-8') < 2) {
        $errores[] = 'El nombre es obligatorio y debe tener al menos 2 caracteres.';
    }

    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo electrónico válido.';
    }

    if (mb_strlen($datos['mensaje'], 'UTF-8') < 5) {
        $errores[] = 'El mensaje o las fechas deben tener al menos 5 caracteres.';
    }

    if (count($errores) === 0) {
        $autoInteres = $datos['auto_interes'] === '' ? null : $datos['auto_interes'];
        $stmt = $pdo->prepare('INSERT INTO contactos (nombre, correo, auto_interes, mensaje) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $datos['nombre'],
            $datos['correo'],
            $autoInteres,
            $datos['mensaje'],
        ]);
        header('Location: contacto.php?enviado=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reserva y contacto · RutaLibre Motors</title>
  <link rel="stylesheet" href="/alquiler/css/estilos.css">
</head>
<body>
<header class="navbar">
  <div class="nav-inner">
    <a class="logo" href="index.php"><span class="logo-mark">🚗</span> RutaLibre Motors</a>
    <nav class="nav-enlaces">
      <a href="index.php#inicio">Inicio</a>
      <a href="index.php#flota">Flota</a>
      <a href="index.php#tarifas">Tarifas</a>
      <a href="contacto.php">Contacto</a>
    </nav>
    <a class="nav-cta" href="index.php#flota">Ver flota</a>
  </div>
</header>

<main class="contenedor">
  <div class="migas"><a href="index.php">Inicio</a> / reserva y contacto</div>

  <?php if (isset($_GET['enviado']) && $_GET['enviado'] == '1'): ?>
    <div class="alerta-exito">
      <strong>¡Solicitud recibida!</strong> Tu reserva fue guardada correctamente. Te contactaremos para confirmar la disponibilidad.
    </div>
  <?php endif; ?>

  <div class="seccion-cabecera">
    <div class="kicker">FORMULARIO DE RESERVA</div>
    <h2>Reservá tu auto</h2>
    <p class="descripcion">Completá tus datos, elegí el auto y contanos las fechas o tus consultas.</p>
  </div>

  <?php if (count($errores) > 0): ?>
    <div class="alerta-error">
      <strong>Revisá estos problemas:</strong>
      <ul>
        <?php foreach ($errores as $error): ?>
          <li><?= e($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form class="formulario" method="post" action="contacto.php" novalidate>
    <div class="form-grid">
      <div class="campo">
        <label for="nombre">Nombre completo</label>
        <input id="nombre" name="nombre" type="text" maxlength="100" value="<?= e($datos['nombre']) ?>" required>
      </div>
      <div class="campo">
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" maxlength="100" value="<?= e($datos['correo']) ?>" required>
      </div>
    </div>

    <div class="campo">
      <label for="auto_interes">Auto de interés</label>
      <select id="auto_interes" name="auto_interes">
        <option value="">Seleccioná un auto (opcional)</option>
        <?php foreach ($modelos as $modelo): ?>
          <option value="<?= e($modelo) ?>" <?= $modelo === $datos['auto_interes'] ? 'selected' : '' ?>>
            <?= e($modelo) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="mensaje">Mensaje / fechas de alquiler</label>
      <textarea id="mensaje" name="mensaje" maxlength="2000" required><?= e($datos['mensaje']) ?></textarea>
    </div>

    <div class="form-acciones">
      <button class="boton" type="submit">Enviar reserva</button>
      <span class="nota">Tus datos se guardan en MySQL con consultas preparadas.</span>
    </div>
  </form>
</main>

<footer class="pie">
  <div class="contenedor pie-inner">
    <div><strong>RutaLibre Motors</strong> · Alquiler de autos en Tarija</div>
    <div>Tecnología Web I · Operación Fogón · UPDS</div>
  </div>
</footer>
</body>
</html>
