<?php
/**
 * Werita 3D — Panel de estadísticas.
 * Acceso: index.php?stats=1 (requiere la contraseña de stats/config.php)
 */

require_once __DIR__ . '/store.php';
require_once __DIR__ . '/config.php';

session_start();
$logged = !empty($_SESSION['werita_stats_ok']);

// Login / logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (hash_equals($WERITA_STATS_PASSWORD, $_POST['password'])) {
        $_SESSION['werita_stats_ok'] = true;
        $logged = true;
    } else {
        $error = 'Contraseña incorrecta';
    }
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if (!$logged):
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stats — Werita 3D</title>
<style>
body{font-family:sans-serif;background:#0f0c29;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.card{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:2.5rem;text-align:center;width:min(90vw,360px)}
h1{color:#8f6bff;font-size:1.4rem;margin-bottom:1.5rem}
input{width:100%;padding:.7rem 1rem;border-radius:8px;border:1px solid #444;background:#1a1440;color:#fff;margin-bottom:1rem;box-sizing:border-box}
button{width:100%;padding:.7rem;border:none;border-radius:8px;background:linear-gradient(90deg,#8f6bff,#ff6bd5);color:#fff;font-weight:bold;cursor:pointer}
.err{color:#ff6b6b;font-size:.85rem;margin-bottom:1rem}
a{color:#8f6bff}
</style>
</head>
<body>
<div class="card">
  <h1>📊 Estadísticas — Werita 3D</h1>
  <?php if (!empty($error)): ?><p class="err"><?= e($error) ?></p><?php endif; ?>
  <form method="post">
    <input type="password" name="password" placeholder="Contraseña" autofocus />
    <button type="submit">Entrar</button>
  </form>
  <p style="margin-top:1.5rem"><a href="index.php">← Volver al sitio</a></p>
</div>
</body>
</html>
<?php
exit;
endif;

// ===== Datos =====
$counters   = werita_load_counters();
$last_visits = werita_tail($GLOBALS['WERITA_VISITS_FILE'], 10);
$last_clicks = werita_tail($GLOBALS['WERITA_CLICKS_FILE'], 10);
$raw_json    = json_encode($counters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$link_names = [
    'tiktok'            => 'TikTok',
    'instagram'         => 'Instagram',
    'facebook'          => 'Facebook',
    'web-werita3d-com'  => 'Werita3D.com',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stats — Werita 3D</title>
<style>
body{font-family:sans-serif;background:#0f0c29;color:#fff;padding:2rem;max-width:960px;margin:auto}
h1{color:#8f6bff} h2{margin-top:2rem;border-bottom:1px solid #333;padding-bottom:.3rem}
table{width:100%;border-collapse:collapse;margin-top:.5rem;font-size:.85rem}
td,th{border:1px solid #333;padding:.4rem .6rem;text-align:left;word-break:break-word}
.big{font-size:2.5rem;font-weight:bold;color:#ff6bd5}
code,pre{background:#1a1440;padding:.1rem .3rem;border-radius:4px}
pre{padding:1rem;overflow:auto}
.top{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
a.btn{color:#fff;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);padding:.5rem 1rem;border-radius:8px;text-decoration:none}
a.btn:hover{background:#8f6bff}
.muted{color:#888;font-size:.8rem}
</style>
</head>
<body>
<div class="top">
  <h1>📊 Estadísticas — Werita 3D</h1>
  <div>
    <a class="btn" href="index.php">Ver sitio</a>
    <a class="btn" href="index.php?stats=1&logout=1">Salir</a>
  </div>
</div>
<p class="muted">Hora mostrada: Ciudad de México (CDMX) · Actualizado cada 30 s</p>
<meta http-equiv="refresh" content="30">

<h2>Total de visitas</h2>
<div class="big"><?= (int)$counters['visits'] ?></div>

<h2>Clics por enlace</h2>
<table>
  <tr><th>Enlace</th><th>Clics</th></tr>
  <?php if ($counters['clicks']): ?>
    <?php arsort($counters['clicks']); foreach ($counters['clicks'] as $k => $v): ?>
      <tr><td><?= e($link_names[$k] ?? $k) ?></td><td><?= (int)$v ?></td></tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="2">Aún no hay clics registrados</td></tr>
  <?php endif; ?>
</table>

<h2>Últimas 10 visitas</h2>
<table>
  <tr><th>Fecha hora CDMX | IP | total | navegador | referencia</th></tr>
  <?php if ($last_visits): foreach ($last_visits as $line): ?>
    <tr><td><?= e($line) ?></td></tr>
  <?php endforeach; else: ?>
    <tr><td>Sin visitas aún</td></tr>
  <?php endif; ?>
</table>

<h2>Últimos 10 clics</h2>
<table>
  <tr><th>Fecha hora CDMX | IP | enlace</th></tr>
  <?php if ($last_clicks): foreach ($last_clicks as $line): ?>
    <tr><td><?= e($line) ?></td></tr>
  <?php endforeach; else: ?>
    <tr><td>Sin clics aún</td></tr>
  <?php endif; ?>
</table>

<h2>Datos crudos (counters.json)</h2>
<pre><?= e($raw_json) ?></pre>

<p class="muted" style="margin-top:2rem">Archivos: <code>stats/visits.txt</code> · <code>stats/clicks.txt</code> · <code>stats/counters.json</code></p>
</body>
</html>
