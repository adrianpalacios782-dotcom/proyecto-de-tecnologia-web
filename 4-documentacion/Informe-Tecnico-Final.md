# Proyecto Final - Plataforma Web y Backend de Gestión de Reservas "RutaLibre Motors"

## 1. CARÁTULA Y METADATOS

| Campo | Detalle |
|---|---|
| Título | Proyecto Final - Plataforma Web y Backend de Gestión de Reservas "RutaLibre Motors" |
| Asignatura | Tecnología Web I · Operación Fogón (FORJA WEB) |
| Unidad académica | UPDS Tarija · FORJA WEB 2026 |
| Estudiante | Adrian Palacios |
| Entorno tecnológico | Docker Desktop, Apache, PHP 8.3, MySQL 8.4, phpMyAdmin, Git/GitHub |
| Repositorio | https://github.com/adrianpalacios782-dotcom/proyecto-de-tecnologia-web |
| Commit de referencia | `299bd7c` — *Reestructurar repo con formato Operacion Fogón y kit Docker autocontenido* |
| Fecha de emisión | 24 de noviembre de 2026 |
| Estado del despliegue | Operativo: `fogon-web`, `fogon-db` (healthy) y `fogon-pma` en ejecución |

---

## 2. INTRODUCCIÓN Y OBJETIVOS

### 2.1 Contexto y problemática

La materia Tecnología Web I plantea la evolución de un sitio estático hacia una aplicación web dinámica capaz de procesar información en el servidor. En este contexto se desarrolla **RutaLibre Motors**, una plataforma de alquiler de autos con presencia en Tarija, Bolivia.

El problema que resuelve el proyecto es doble:

1. **Comunicación estática insuficiente.** Un catálogo en HTML puro solo muestra información, pero no permite registrar intenciones de reserva ni conservar los datos ingresados por el visitante.
2. **Ausencia de persistencia y controles.** Sin backend ni base de datos, no es posible auditar solicitudes, validar campos en servidor ni proteger la aplicación frente a entradas maliciosas.

La solución implementada combina un catálogo generado dinámicamente en PHP con un formulario de reserva que valida, sanea y persiste cada solicitud en MySQL, aplicando buenas prácticas de seguridad y un flujo de navegación que evita envíos duplicados.

### 2.2 Objetivo general

Pasar de un sitio web estático a una **plataforma viva** con procesamiento dinámico en servidor, validaciones estrictas y persistencia en base de datos, desplegada en un entorno Docker reproducible y documentado.

### 2.3 Objetivos específicos

- Generar el catálogo de vehículos desde un array PHP usando `foreach`, demostrando que el HTML permanece estable mientras los datos cambian.
- Diseñar la tabla `contactos` con seis columnas y una estrategia de inicialización en frío segura e idempotente.
- Implementar conexión PDO con manejo de excepciones `try / catch` y Configuración de errores estricta.
- Procesar el formulario exclusivamente por el método HTTP POST y validar nombre, correo y mensaje en servidor.
- Neutralizar inyecciones SQL mediante consultas preparadas con marcadores posicionales.
- Mitigar Cross-Site Scripting (XSS) mediante escape de salida con `htmlspecialchars()`.
- Aplicar el patrón Post-Redirect-Get (PRG) con código HTTP 302 para impedir duplicación por recarga F5.
- Empaquetar el entorno en Docker Compose con montaje nativo de volúmenes, sin symlinks ni junctions de Windows.
- Verificar el sistema con una batería de pruebas funcionales, negativas y de seguridad, y publicar el resultado en GitHub.

### 2.4 Metáfora arquitectónica del curso

| Metáfora | Tecnología | Función en la aplicación | Entrada / interfaz |
|---|---|---|---|
| El **mozo** | Apache (dentro de `fogon-web`) | Recibe la petición HTTP y entrega la respuesta al navegador | Puerto `8090` |
| El **cocinero** | PHP 8.3 | Procesa datos, valida, decide y genera HTML dinámico | `index.php`, `contacto.php`, `conexion.php` |
| La **despensa** | MySQL 8.4 | Almacena de forma persistente las reservas registradas | Red interna `db:3306`, host `127.0.0.1:3308` |
| La **ventana de la despensa** | phpMyAdmin 5 | Permite inspeccionar tablas y datos desde el navegador | Puerto `8091` |

---

## 3. ARQUITECTURA DEL SISTEMA Y ENTORNO DOCKER

### 3.1 Contenedores de Docker Compose

| Servicio | Contenedor | Imagen / build | Publicación | Responsabilidad |
|---|---|---|---|---|
| `web` | `fogon-web` | `./docker/php` (PHP 8.3 + Apache + `pdo_mysql`) | `0.0.0.0:8090 -> 80` | Sirve la aplicación `www/alquiler` |
| `db` | `fogon-db` | `mysql:8.4` | `127.0.0.1:3308 -> 3306` | Base de datos `fogon`, volumen `fogon-datos` |
| `phpmyadmin` | `fogon-pma` | `phpmyadmin:5` | `0.0.0.0:8091 -> 80` | Administración visual de MySQL |

El archivo `docker-compose.yml` declara las variables `DB_HOST: db`, `DB_NAME: fogon`, `DB_USER: fogon` y `DB_PASS: fogon123` para el servicio web, y un healthcheck basado en `mysqladmin ping` que garantiza que PHP solo arranque cuando MySQL está listo (`depends_on.condition: service_healthy`).

### 3.2 Red, volúmenes y configuración

- **Red:** la red por defecto `operacion-fogon_default` permite que `web` resuelva el hostname `db`.
- **Volumen:** `fogon-datos` conserva `/var/lib/mysql` entre reinicios.
- **Montaje de código:** `./www:/var/www/html`, por lo que la ruta pública del proyecto es `http://localhost:8090/alquiler/`.
- **Inicialización:** `./db/init.sql` se monta en `/docker-entrypoint-initdb.d/01-init.sql` y se ejecuta solo la primera vez que se crea el volumen.

### 3.3 Decisión DevOps: montaje nativo frente a symlinks/junctions

Durante el desarrollo se intentó mantener el código en `D:\proyectofinal\www\alquiler` y enlazarlo dentro del kit del curso mediante un junction de Windows. La decisión fue **descartar ese enfoque** por tres razones técnicas:

1. Docker Desktop interpretó el junction como un symlink dentro del contenedor y resolvió una ruta inexistente:
   - `Could not open input file: /var/www/html/alquiler/index.php`
   - `ls: cannot access '/mnt/host/d/proyectofinal/www/alquiler': No such file or directory`
2. La creación del enlace también encontró una restricción de permisos: `New-Item : Acceso denegado a la ruta de acceso 'alquiler'.`, resuelta temporalmente con `icacls "D:\proyectofinal" /grant "$($env:USERNAME):(OI)(CI)F" /T`.
3. Un `docker-compose.override.yml` con la ruta absoluta de Windows funcionaba en la máquina local, pero rompía la portabilidad en Linux y añadía una dependencia frágil del entorno del estudiante.

La solución definitiva fue reorganizar el repositorio para que el código viva físicamente en `2-kit-docker/operacion-fogon/www/alquiler/` y que el compose original monte `./www:/var/www/html`. De esta forma:

- No hay symlinks ni junctions: la verificación de `LinkType` sobre todo el árbol devolvió **0 elementos**.
- No hay override en el kit entregado: `docker-compose.override.yml` está ausente.
- El repositorio se puede clonar en Windows, Linux o macOS y ejecutar con `docker compose up -d --build` sin editar rutas.

### 3.4 Organización final del repositorio

```
proyecto-de-tecnologia-web/
├── 1-PDFs/
│   ├── 1-presentacion-fogon.pdf
│   ├── 2-guia-estudiante.pdf
│   ├── 3-chuleta-php.pdf
│   └── 4-guion-docente.pdf
├── 2-kit-docker/operacion-fogon/
│   ├── docker-compose.yml
│   ├── README.md
│   ├── db/init.sql
│   ├── docker/php/{Dockerfile, php-dev.ini}
│   ├── scripts/{apagar.ps1, iniciar.ps1, iniciar.sh,
│   │           plan-b-sin-base.ps1, reiniciar-base.ps1, reiniciar-base.sh}
│   └── www/alquiler/
│       ├── conexion.php
│       ├── contacto.php
│       ├── index.php
│       ├── tabla.sql
│       └── css/estilos.css
├── 3-fuentes-latex/
│   ├── estilo-jpcruz.sty
│   ├── fogon-chuleta.tex
│   ├── fogon-guia-estudiante.tex
│   ├── fogon-guion-docente.tex
│   ├── fogon-presentacion.tex
│   ├── g-apendices.tex
│   ├── g-intro.tex
│   ├── g-niveles.tex
│   └── imagenes/ (14 PNG)
└── 4-documentacion/
    └── Informe-Tecnico-Final.md
```

| Área | Contenido | Cantidad |
|---|---|---|
| `1-PDFs/` | Documentación oficial del curso | 4 PDF |
| `2-kit-docker/operacion-fogon/` | Contenedores, scripts, base de datos y aplicación web | 11 archivos de infraestructura + 5 de aplicación |
| `3-fuentes-latex/` | Fuentes `.tex`, estilo y recursos gráficos | 7 `.tex` + 1 `.sty` + 14 PNG |
| `4-documentacion/` | Informe técnico y guía operativa | 1 Markdown |

---

## 4. DESARROLLO PASO A PASO (EXPLICACIÓN TÉCNICA)

### 4.1 Fase 1: Frontend y catálogo dinámico (`index.php` y `css/estilos.css`)

El catálogo no se escribió como HTML repetido, sino como un **array de vehículos** recorrido con `foreach`. Cada elemento contiene modelo, categoría, precio diario, características, etiqueta opcional e icono:

```php
$autos = [
    [
        'modelo' => 'Suzuki Swift',
        'categoria' => 'Económico',
        'precio_dia' => 180,
        'caracteristicas' => ['pasajeros' => 4, 'transmision' => 'Manual', 'ac' => 'Sí'],
        'etiqueta' => 'Mejor precio',
        'icono' => '🚗',
    ],
    // ... Toyota Corolla, Toyota RAV4 y Toyota Hilux
];

<?php foreach ($autos as $auto): ?>
  <article class="tarjeta-auto">
    <h3 class="auto-titulo"><?= e($auto['modelo']) ?></h3>
    <p class="auto-precio"><strong>Bs <?= e(number_format($auto['precio_dia'], 2, '.', ',')) ?></strong><small>/ día</small></p>
    <a class="boton-reservar" href="contacto.php?auto=<?= e(urlencode($auto['modelo'])) ?>">Reservar este auto</a>
  </article>
<?php endforeach; ?>
```

**Principio demostrado:** el código HTML queda intacto mientras los datos cambian dinámicamente. Agregar o quitar un vehículo solo requiere modificar el array; la plantilla de presentación no se toca. Además, la función auxiliar `e()` aplica `htmlspecialchars()` a cada valor emitido, incluidos los que llegan por GET como `?auto=...`.

La hoja `css/estilos.css` define una paleta marino/naranja con variables CSS (`--marino`, `--naranja`, `--verde-fondo`, `--rojo-fondo`), navbar fija, hero, grilla de tarjetas, formulario, alertas de éxito/error y media queries para adaptarse a móvil y escritorio.

### 4.2 Fase 2: Persistencia y base de datos (`conexion.php` y `db/init.sql`)

La tabla `contactos` quedó definida con seis columnas:

| Columna | Tipo | Restricción | Propósito |
|---|---|---|---|
| `id` | `INT AUTO_INCREMENT` | `PRIMARY KEY` | Identificador único de la reserva |
| `nombre` | `VARCHAR(100)` | `NOT NULL` | Nombre completo del solicitante |
| `correo` | `VARCHAR(100)` | `NOT NULL` | Correo de contacto validado |
| `auto_interes` | `VARCHAR(100)` | `NULL` | Modelo seleccionado desde el catálogo |
| `mensaje` | `TEXT` | `NOT NULL` | Fechas o consultas del cliente |
| `creado` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Marca de tiempo automática |

`tabla.sql` contiene la definición canónica:

```sql
DROP TABLE IF EXISTS contactos;

CREATE TABLE IF NOT EXISTS contactos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  auto_interes VARCHAR(100) NULL,
  mensaje TEXT NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Como `db/init.sql` del kit original creaba una tabla `contactos` más antigua (sin `auto_interes`), se añadió al final del script de inicialización la misma estrategia `DROP TABLE IF EXISTS` + `CREATE TABLE IF NOT EXISTS`. Así, un despliegue desde cero genera directamente el esquema correcto sin importación manual posterior.

La conexión usa PDO con errores estrictos y modo associative fetch:

```php
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
    // ... respuesta HTML con el detalle escapado y exit;
}
```

El `try / catch` evita que una falla de MySQL produzca un error PHP expuesto al usuario: se devuelve HTTP 500 y un mensaje controlado, con el detalle técnico escapado mediante `htmlspecialchars()`.

### 4.3 Fase 3: Backend de contacto, seguridad y validaciones (`contacto.php`)

#### Uso exclusivo de POST

El formulario declara `method="post" action="contacto.php"`. El procesamiento solo ocurre cuando `$_SERVER['REQUEST_METHOD'] === 'POST'`, de modo que los datos sensibles no quedan expuestos en la URL ni en el historial del navegador.

#### Saneado y validación en servidor

```php
function entrada($clave) {
    $valor = $_POST[$clave] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

if (mb_strlen($datos['nombre'], 'UTF-8') < 2) {
    $errores[] = 'El nombre es obligatorio y debe tener al menos 2 caracteres.';
}
if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Ingresá un correo electrónico válido.';
}
if (mb_strlen($datos['mensaje'], 'UTF-8') < 5) {
    $errores[] = 'El mensaje o las fechas deben tener al menos 5 caracteres.';
}
```

| Control | Función | Finalidad |
|---|---|---|
| Recorte de espacios | `trim()` | Evita valores compuestos solo por espacios |
| Longitud multibyte | `mb_strlen(..., 'UTF-8')` | Cuenta correctamente tildes y caracteres latinos |
| Formato de correo | `filter_var(..., FILTER_VALIDATE_EMAIL)` | Rechaza direcciones mal formadas |
| Array de errores | `$errores[]` | Centraliza la retroalimentación para la vista |

Cuando hay errores, se renderiza una caja `.alerta-error` con un `<ul>` de mensajes y el formulario se vuelve a mostrar con los valores ya ingresados (`value="<?= e($datos['nombre']) ?>"`, `<textarea><?= e($datos['mensaje']) ?></textarea>`), preservando la selección de auto mediante `selected`.

#### Consulta preparada contra inyección SQL

```php
$autoInteres = $datos['auto_interes'] === '' ? null : $datos['auto_interes'];
$stmt = $pdo->prepare('INSERT INTO contactos (nombre, correo, auto_interes, mensaje) VALUES (?, ?, ?, ?)');
$stmt->execute([
    $datos['nombre'],
    $datos['correo'],
    $autoInteres,
    $datos['mensaje'],
]);
```

Los marcadores `?` separan la estructura SQL de los datos. El payload `Ana'); DROP TABLE contactos; --` se almacenó literalmente como texto en `nombre`, sin alterar la tabla: evidencia directa de que la inyección fue neutralizada.

#### Patrón Post-Redirect-Get (PRG)

```php
header('Location: contacto.php?enviado=1');
exit;
```

Tras un INSERT correcto, el servidor responde `302 Found` y redirige a una URL GET. Si el usuario presiona F5, recarga la página de confirmación y no vuelve a ejecutar el INSERT. La vista muestra entonces `.alerta-exito` cuando `?enviado=1` está presente.

#### Mitigación de XSS

La función `e()` se aplica tanto a datos del catálogo como a datos del formulario y mensajes de error:

```php
function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
```

La prueba con `<script>alert(1)</script>` devolvió `&lt;script&gt;alert(1)&lt;/script&gt;` en el HTML, sin ejecución de script.

| Técnica | Riesgo mitigado | Evidencia |
|---|---|---|
| Consultas preparadas | Inyección SQL | Payload guardado como texto plano |
| `htmlspecialchars()` | XSS reflejado | Entidades HTML en la respuesta |
| Validación en servidor | Datos inválidos o vacíos | 2 errores en prueba negativa |
| PRG 302 | Duplicación por F5 | `Location: contacto.php?enviado=1` |
| `require __DIR__` | Rutas frágiles |Funciona tras mover el proyecto dentro del kit |

---

## 5. GUÍA OPERATIVA Y COMANDOS DE EJECUCIÓN (CMD / POWERSHELL)

Ubicarse en la carpeta del kit:

```powershell
cd D:\proyectofinal\2-kit-docker\operacion-fogon
```

Encender y construir:

```powershell
docker compose up -d --build
```

Verificar salud de contenedores:

```powershell
docker compose ps
docker inspect -f "{{.State.Health.Status}}" fogon-db
```

Auditar registros del servidor web:

```powershell
docker compose logs -f web
```

Detener los contenedores (sin borrar el volumen de datos):

```powershell
docker compose down
```

Verificar sintaxis PHP dentro del contenedor:

```powershell
docker compose exec -T web php -l /var/www/html/alquiler/index.php
docker compose exec -T web php -l /var/www/html/alquiler/contacto.php
docker compose exec -T web php -l /var/www/html/alquiler/conexion.php
```

Importar el esquema canónico en MySQL:

```powershell
Get-Content -Raw .\www\alquiler\tabla.sql | docker compose exec -T db mysql -uroot -proot123 fogon
```

Certificar conexión PDO desde PHP (compatible con CMD y PowerShell):

```powershell
docker compose exec -T web php -r "require '/var/www/html/alquiler/conexion.php'; echo 'PDO_OK';"
```

Inspeccionar la tabla:

```powershell
docker compose exec -T db mysql -ufogon -pfogon123 -e "SHOW COLUMNS FROM contactos;" fogon
```

Entrar a los contenedores de Docker desde CMD o PowerShell (modo interactivo):

```powershell
# Entrar a la terminal (bash) del contenedor web (Apache + PHP) — salir con: exit
docker compose exec web bash

# Entrar a la consola interactiva de MySQL (base de datos fogon) — salir con: exit;
docker compose exec db mysql -ufogon -pfogon123 fogon
```

Consultar las reservas guardadas directamente desde CMD o PowerShell:

```powershell
docker compose exec -T db mysql -ufogon -pfogon123 -e "SELECT * FROM contactos;" fogon
```

URLs de acceso:

- Catálogo: `http://localhost:8090/alquiler/index.php`
- Formulario: `http://localhost:8090/alquiler/contacto.php`
- Confirmación: `http://localhost:8090/alquiler/contacto.php?enviado=1`
- phpMyAdmin: `http://localhost:8091` (`fogon` / `fogon123`)

---

## 6. BATERÍA DE PRUEBAS Y CONTROL DE CALIDAD (QA)

| ID | Prueba | Procedimiento | Resultado esperado | Resultado |
|---|---|---|---|---|
| QA-01 | Pre-flight de fuentes | Verificar existencia de las 4 carpetas de origen | Todas presentes | **PASS** |
| QA-02 | Estado Git limpio | `git status --porcelain` antes de mover | 0 cambios | **PASS** |
| QA-03 | Copia sin sobrescritura | Copia con comparación de hash SHA256 | Sin conflictos ni duplicados | **PASS** |
| QA-04 | Sintaxis PHP | `php -l` en los 3 scripts | `No syntax errors detected` | **PASS** |
| QA-05 | Contenedores | `docker compose ps` | `web` running, `db` healthy, `pma` running | **PASS** |
| QA-06 | Catálogo HTTP | GET a `index.php` | HTTP 200 y 4 modelos presentes | **PASS** |
| QA-07 | Formulario HTTP | GET a `contacto.php` | HTTP 200 | **PASS** |
| QA-08 | Confirmación HTTP | GET a `contacto.php?enviado=1` | HTTP 200 y texto "Solicitud recibida" | **PASS** |
| QA-09 | Validación negativa | POST con nombre corto y correo inválido | 2 mensajes en `.alerta-error` | **PASS** |
| QA-10 | Inyección SQL | POST con `Ana'); DROP TABLE contactos; --` | Texto literal en MySQL, tabla intacta | **PASS** |
| QA-11 | XSS | POST con `<script>alert(1)</script>` | HTML con `&lt;script&gt;...` | **PASS** |
| QA-12 | PRG / F5 | POST válido y revisión de cabeceras | `302` + `Location: contacto.php?enviado=1` | **PASS** |
| QA-13 | Conexión PDO | `SELECT 1` desde PHP en el contenedor | Devuelve `1` | **PASS** |
| QA-14 | Esquema de datos | `SHOW COLUMNS FROM contactos` | 6 columnas con `auto_interes` | **PASS** |
| QA-15 | Sin enlaces frágiles | Revisión de `LinkType` en todo el árbol | 0 symlinks/junctions | **PASS** |
| QA-16 | Kit limpio | Revisión remota vía API de GitHub | 0 override, 0 ejercicios, 5 archivos en `www/alquiler` | **PASS** |
| QA-17 | Persistencia y limpieza | POST válido, verificación y `DELETE` de pruebas | Fila insertada y luego 0 filas | **PASS** |

Evidencias representativas:

```text
HTTP_index=200 autos_faltantes=
HTTP_contacto=200
HTTP_css=200
PDO_SELECT1=1
COLUMNAS=id,nombre,correo,auto_interes,mensaje,creado
errores_validacion=2
xss_escapado=True
POST_VALIDO=302 http://localhost:8090/alquiler/contacto.php?enviado=1
filas=0
REMOTE_FILES=42 REMOTE_OVERRIDE=False REMOTE_EJERCICIOS=0 REMOTE_ALQUILER=5
```

---

## 7. CONCLUSIONES

El proyecto cumple el salto exigido por la asignatura: de un sitio estático a una plataforma con backend desacoplado, seguro y persistente. El catálogo demuestra que la presentación puede desacoplarse de los datos mediante un array PHP y un `foreach`; el formulario demuestra que la validación debe ocurrir en servidor, no solo en el navegador; y la capa PDO garantiza que la información viaje a MySQL sin exponer la estructura SQL.

Las decisiones DevOps también forman parte del aprendizaje: abandonar junctions de Windows y el override absoluto en favor del montaje nativo `./www:/var/www/html` entregó un repositorio portable, reproducible y libre de dependencias del equipo de desarrollo. La reorganización en cuatro áreas (`1-PDFs`, `2-kit-docker`, `3-fuentes-latex` y `4-documentacion`) deja el entregable completo y alineado con la estructura del curso.

La batería de QA confirmó los 17 controles con resultado **PASS**, incluyendo pruebas negativas de validación, inyección SQL, XSS y duplicación por F5. El sistema queda operativo en `http://localhost:8090/alquiler/` con phpMyAdmin en `http://localhost:8091`.

**Repositorio final:** https://github.com/adrianpalacios782-dotcom/proyecto-de-tecnologia-web · **commit `299bd7c`**.
