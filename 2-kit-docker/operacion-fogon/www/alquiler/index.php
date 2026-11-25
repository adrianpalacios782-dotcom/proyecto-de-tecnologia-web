<?php
function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$autos = [
    [
        'modelo' => 'Suzuki Swift',
        'categoria' => 'Económico',
        'precio_dia' => 180,
        'caracteristicas' => ['pasajeros' => 4, 'transmision' => 'Manual', 'ac' => 'Sí'],
        'etiqueta' => 'Mejor precio',
        'icono' => '🚗',
        'imagen' => 'img/suzuki-swift.jpg',
    ],
    [
        'modelo' => 'Toyota Corolla',
        'categoria' => 'Sedán',
        'precio_dia' => 260,
        'caracteristicas' => ['pasajeros' => 5, 'transmision' => 'Automática', 'ac' => 'Sí'],
        'etiqueta' => 'Más popular',
        'icono' => '🚙',
        'imagen' => 'img/toyota-corolla.jpg',
    ],
    [
        'modelo' => 'Toyota RAV4',
        'categoria' => 'SUV Familiar',
        'precio_dia' => 390,
        'caracteristicas' => ['pasajeros' => 5, 'transmision' => 'Automática', 'ac' => 'Sí'],
        'etiqueta' => null,
        'icono' => '🚐',
        'imagen' => 'img/toyota-rav4.jpg',
    ],
    [
        'modelo' => 'Toyota Hilux',
        'categoria' => 'Camioneta 4x4',
        'precio_dia' => 450,
        'caracteristicas' => ['pasajeros' => 5, 'transmision' => 'Manual', 'ac' => 'Sí'],
        'etiqueta' => 'Para rutas largas',
        'icono' => '🛻',
        'imagen' => 'img/toyota-hilux.jpg',
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RutaLibre Motors · Alquiler de autos en Tarija</title>
  <link rel="stylesheet" href="/alquiler/css/estilos.css">
</head>
<body>
<header class="navbar">
  <div class="nav-inner">
    <a class="logo" href="index.php"><span class="logo-mark">🚗</span> RutaLibre Motors</a>
    <nav class="nav-enlaces">
      <a href="#inicio">Inicio</a>
      <a href="#flota">Flota</a>
      <a href="#tarifas">Tarifas</a>
      <a href="contacto.php">Contacto</a>
    </nav>
    <a class="nav-cta" href="contacto.php">Reservar ahora</a>
  </div>
</header>

<main>
  <section class="hero" id="inicio">
    <div class="contenedor hero-inner">
      <div>
        <div class="kicker">RENT A CAR · TARIJA · BOLIVIA</div>
        <h1>Tu viaje empieza aquí <span>- Alquila autos al mejor precio en Tarija</span></h1>
        <p class="hero-subtitulo">Flota revisada, precios claros en bolivianos y reserva en minutos. Elegí tu auto y salí a recorrer.</p>
        <div class="hero-accion">
          <a class="boton" href="#flota">Ver la flota</a>
          <a class="boton-secundario" href="contacto.php">Hacer una reserva</a>
        </div>
        <p class="nota">Página generada en el servidor con PHP <?= e(PHP_VERSION) ?> · <?= e(date('d/m/Y H:i:s')) ?></p>
      </div>
      <aside class="hero-panel">
        <h3>Por qué RutaLibre Motors</h3>
        <ul>
          <li>Entrega y devolución en Tarija</li>
          <li>Tarifas por día en Bs, sin sorpresas</li>
          <li>Autos con aire acondicionado</li>
          <li>Atención personalizada</li>
        </ul>
      </aside>
    </div>
  </section>

  <section class="seccion" id="flota">
    <div class="contenedor">
      <div class="seccion-cabecera">
        <div class="kicker">NUESTRA FLOTA</div>
        <h2>Elegí el auto para tu ruta</h2>
        <p class="descripcion">Estas tarjetas las genera PHP con un <code>foreach</code> sobre un array de vehículos.</p>
      </div>
      <div class="grid-autos">
        <?php foreach ($autos as $auto): ?>
          <article class="tarjeta-auto">
            <div class="auto-visual">
              <img class="auto-foto" src="<?= e($auto['imagen']) ?>" alt="<?= e($auto['modelo']) ?>" loading="lazy">
              <?php if (!empty($auto['etiqueta'])): ?>
                <span class="etiqueta"><?= e($auto['etiqueta']) ?></span>
              <?php endif; ?>
            </div>
            <div class="auto-cuerpo">
              <span class="auto-categoria"><?= e($auto['categoria']) ?></span>
              <h3 class="auto-titulo"><?= e($auto['modelo']) ?></h3>
              <ul class="auto-lista">
                <li><?= e($auto['caracteristicas']['pasajeros']) ?> pasajeros</li>
                <li>Transmisión <?= e($auto['caracteristicas']['transmision']) ?></li>
                <li>Aire acondicionado: <?= e($auto['caracteristicas']['ac']) ?></li>
              </ul>
              <p class="auto-precio"><strong>Bs <?= e(number_format($auto['precio_dia'], 2, '.', ',')) ?></strong><small>/ día</small></p>
              <a class="boton-reservar" href="contacto.php?auto=<?= e(urlencode($auto['modelo'])) ?>">Reservar este auto</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="seccion" id="tarifas">
    <div class="contenedor">
      <div class="seccion-cabecera">
        <div class="kicker">TARIFAS</div>
        <h2>Todo incluido en el precio por día</h2>
      </div>
      <div class="grid-beneficios">
        <div class="beneficio"><h3>Kilometraje</h3><p>Consultá condiciones de kilometraje según el auto elegido.</p></div>
        <div class="beneficio"><h3>Seguro básico</h3><p>Cobertura incluida en todas las tarifas de la flota.</p></div>
        <div class="beneficio"><h3>Forma de pago</h3><p>Podés coordinar el pago al momento de la reserva.</p></div>
      </div>
    </div>
  </section>

  <section class="seccion" id="contacto">
    <div class="contenedor cta">
      <h2>¿Listo para salir?</h2>
      <p>Completa el formulario de reserva y te confirmamos la disponibilidad.</p>
      <a class="boton" href="contacto.php">Ir al formulario de contacto</a>
    </div>
  </section>
</main>

<footer class="pie">
  <div class="contenedor pie-inner">
    <div><strong>RutaLibre Motors</strong> · Alquiler de autos en Tarija</div>
    <div>Tecnología Web I · Operación Fogón · UPDS</div>
  </div>
</footer>
</body>
</html>
