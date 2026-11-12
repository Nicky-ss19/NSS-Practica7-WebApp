<?php
// Mi app Hola Mundo para la Práctica 7 de Electiva 2 (DevOps)
// Se conecta a la base de datos MySQL que corre en otro contenedor

// Los datos de conexión vienen de las variables de entorno del docker-compose.yml
// Así no escribo la contraseña directamente en el código
$host = getenv('DB_HOST') ?: 'db';
$nombreBd = getenv('DB_NAME') ?: 'practica7';
$usuario = getenv('DB_USER') ?: 'nicky';
$clave = getenv('DB_PASSWORD') ?: '';

$conectado = false;
$error = '';
$versionMysql = '';
$mensajes = [];

try {
    // PDO es la forma que tiene PHP para hablar con bases de datos
    $pdo = new PDO(
        "mysql:host=$host;dbname=$nombreBd;charset=utf8mb4",
        $usuario,
        $clave,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $conectado = true;

    // Si alguien envió el formulario, guardo el mensaje nuevo
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $autor = trim($_POST['autor'] ?? '');
        $texto = trim($_POST['mensaje'] ?? '');

        if ($autor !== '' && $texto !== '') {
            // Uso prepare para que nadie pueda meter código SQL raro (inyección SQL)
            $guardar = $pdo->prepare('INSERT INTO mensajes (autor, mensaje) VALUES (?, ?)');
            $guardar->execute([mb_substr($autor, 0, 60), mb_substr($texto, 0, 255)]);
        }

        // Recargo la página para que el mensaje no se envíe dos veces
        header('Location: /');
        exit;
    }

    // Le pregunto a MySQL su versión y traigo los mensajes, del más nuevo al más viejo
    $versionMysql = $pdo->query('SELECT VERSION()')->fetchColumn();
    $mensajes = $pdo->query('SELECT autor, mensaje, creado_en FROM mensajes ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    $error = $ex->getMessage();
}

// Para mostrar en la página lo que escribió la gente, sin que se cuele código raro
function e($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- Para que se vean bien los acentos y la ñ -->
  <meta charset="UTF-8">
  <!-- Para que la página se adapte al celular -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hola Mundo con MySQL | Práctica 7</title>

  <!-- Dancing Script para los títulos, Inter para el texto y JetBrains Mono para los datos -->
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Inter:wght@400;600;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

  <style>
    /* Mis colores para esta práctica: negro, verde y azul */
    :root {
      --fondo: #0a0e13;
      --panel: #121922;
      --panel-claro: #1a2430;
      --verde: #22c55e;
      --azul: #38bdf8;
      --rojo: #f87171;
      --texto: #e6edf3;
      --gris: #8b98a8;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: var(--fondo);
      color: var(--texto);
      line-height: 1.6;
    }

    /* Arriba: el saludo, con un brillito azul de fondo */
    header {
      text-align: center;
      padding: 72px 20px 48px;
      background: radial-gradient(ellipse at top, rgba(56, 189, 248, 0.15), transparent 60%);
    }

    .etiqueta {
      color: var(--azul);
      font-size: 0.8rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      font-weight: 600;
    }

    /* Los títulos con letra cursiva */
    h1, h2 {
      font-family: 'Dancing Script', cursive;
      font-weight: 700;
    }

    /* El Hola Mundo con un degradado de verde a azul */
    h1 {
      font-size: clamp(3rem, 8vw, 5rem);
      margin: 8px 0;
      background: linear-gradient(90deg, var(--verde), var(--azul));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    header p { color: var(--gris); margin: 0; }

    main {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 20px 60px;
    }

    section { margin-top: 56px; }

    h2 {
      font-size: 2.2rem;
      margin: 0 0 16px;
      color: var(--azul);
    }

    /* Los tres datos del estado de la conexión */
    .estado {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
    }

    .dato {
      background: var(--panel);
      border-radius: 12px;
      padding: 20px;
    }

    .dato span {
      display: block;
      color: var(--gris);
      font-size: 0.85rem;
    }

    .dato strong {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.15rem;
    }

    /* El puntito verde que "late" cuando hay conexión */
    .punto {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 8px;
      background: var(--verde);
      animation: latir 1.6s infinite;
    }

    /* Y si no hay conexión, se pone rojo y quieto */
    .punto.mal {
      background: var(--rojo);
      animation: none;
    }

    @keyframes latir {
      0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
      70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
      100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    .error {
      margin-top: 12px;
      background: rgba(248, 113, 113, 0.1);
      color: var(--rojo);
      padding: 14px 18px;
      border-radius: 10px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.85rem;
    }

    /* El recorrido: navegador, app y base de datos, con puntitos que viajan */
    .recorrido {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
    }

    .caja {
      background: var(--panel);
      border-radius: 12px;
      padding: 16px 20px;
      flex: 1;
      min-width: 160px;
      text-align: center;
    }

    .caja small {
      display: block;
      color: var(--gris);
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.8rem;
    }

    .caja.app strong { color: var(--verde); }
    .caja.db strong { color: var(--azul); }

    .camino {
      position: relative;
      width: 60px;
      height: 2px;
      background: var(--panel-claro);
    }

    .camino::after {
      content: '';
      position: absolute;
      top: -3px;
      left: 0;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--verde);
      animation: viajar 2s linear infinite;
    }

    .camino.azul::after {
      background: var(--azul);
      animation-delay: 1s;
    }

    @keyframes viajar {
      from { left: 0; }
      to { left: calc(100% - 8px); }
    }

    /* El formulario para dejar mensajes */
    form {
      display: grid;
      gap: 10px;
      background: var(--panel);
      padding: 20px;
      border-radius: 12px;
    }

    input, textarea {
      width: 100%;
      background: var(--fondo);
      color: var(--texto);
      border: 1px solid var(--panel-claro);
      border-radius: 8px;
      padding: 12px;
      font: inherit;
    }

    input:focus, textarea:focus {
      outline: 2px solid var(--azul);
      border-color: transparent;
    }

    button {
      justify-self: start;
      background: linear-gradient(90deg, var(--verde), var(--azul));
      color: var(--fondo);
      border: none;
      border-radius: 8px;
      padding: 12px 22px;
      font-weight: 800;
      cursor: pointer;
    }

    button:hover { filter: brightness(1.1); }

    /* La lista de mensajes que vienen de MySQL, con una barrita de color */
    .mensajes {
      list-style: none;
      padding: 0;
      margin: 0;
      display: grid;
      gap: 10px;
    }

    .mensajes li {
      background: var(--panel);
      border-radius: 12px;
      padding: 16px 20px;
      border-left: 3px solid var(--verde);
    }

    .mensajes li:nth-child(even) { border-left-color: var(--azul); }

    .meta {
      color: var(--gris);
      font-size: 0.8rem;
      font-family: 'JetBrains Mono', monospace;
    }

    footer {
      text-align: center;
      color: var(--gris);
      padding: 32px 20px;
      font-size: 0.9rem;
    }

    footer strong { color: var(--verde); }

    /* En el celular, las cajitas del recorrido van una debajo de la otra */
    @media (max-width: 600px) {
      .camino { width: 100%; height: 16px; background: none; }
      .camino::after { display: none; }
    }

    /* Si la persona prefiere menos movimiento, apagamos las animaciones */
    @media (prefers-reduced-motion: reduce) {
      .punto, .camino::after { animation: none; }
    }
  </style>
</head>
<body>

  <!-- El encabezado con el Hola Mundo -->
  <header>
    <div class="etiqueta">Práctica 7 · Electiva 2 (DevOps) · ITLA</div>
    <h1>Hola Mundo</h1>
    <p>Una app en PHP conectada a MySQL, cada una en su propio contenedor.</p>
  </header>

  <main>
    <!-- El estado de la conexión: si funcionó, la versión y cuántos mensajes hay -->
    <section>
      <h2>¿Se conectó?</h2>
      <div class="estado">
        <div class="dato">
          <span>Conexión a MySQL</span>
          <strong><i class="punto <?= $conectado ? '' : 'mal' ?>"></i><?= $conectado ? 'Conectada' : 'Sin conexión' ?></strong>
        </div>
        <div class="dato">
          <span>Versión de MySQL</span>
          <strong><?= $conectado ? e($versionMysql) : '-' ?></strong>
        </div>
        <div class="dato">
          <span>Mensajes guardados</span>
          <strong><?= count($mensajes) ?></strong>
        </div>
      </div>

      <?php if (!$conectado): ?>
        <div class="error">Error: <?= e($error) ?></div>
      <?php endif; ?>
    </section>

    <!-- El dibujito del recorrido de la información -->
    <section>
      <h2>¿Cómo viaja la información?</h2>
      <div class="recorrido">
        <div class="caja"><strong>Tu navegador</strong><small>puerto 8080</small></div>
        <div class="camino"></div>
        <div class="caja app"><strong>app</strong><small>php:8.3-apache</small></div>
        <div class="camino azul"></div>
        <div class="caja db"><strong>db</strong><small>mysql:8.4 · puerto 3306</small></div>
      </div>
    </section>

    <!-- El formulario: lo que escribas aquí se guarda en MySQL -->
    <section>
      <h2>Deja un mensaje</h2>
      <form method="post">
        <input type="text" name="autor" placeholder="Tu nombre" maxlength="60" required>
        <textarea name="mensaje" rows="3" placeholder="Escribe algo, se va a guardar en MySQL" maxlength="255" required></textarea>
        <button type="submit">Guardar en MySQL</button>
      </form>
    </section>

    <!-- Los mensajes que están guardados en la base de datos -->
    <section>
      <h2>Lo que hay en la base de datos</h2>
      <?php if ($mensajes): ?>
        <ul class="mensajes">
          <?php foreach ($mensajes as $m): ?>
            <li>
              <div><?= e($m['mensaje']) ?></div>
              <div class="meta"><?= e($m['autor']) ?> · <?= e($m['creado_en']) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="meta">Todavía no hay mensajes.</p>
      <?php endif; ?>
    </section>
  </main>

  <footer><strong>Nicole Alejandra Santana Sánchez</strong> · 2025-1213 · Prof. Elvis Cruz</footer>

</body>
</html>