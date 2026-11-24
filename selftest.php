<?php
/**
 * Werita 3D — Selftest de diagnóstico del contador.
 *
 * Abre:  https://tudominio.com/selftest.php?key=werita2026
 * (la llave está en stats/config.php, $WERITA_STATS_PASSWORD)
 *
 * Te dice si PHP puede escribir en stats/, si el registro de visitas y
 * clics funciona, y muestra las últimas líneas de los logs.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/stats/store.php';

// --- autenticación simple con la misma contraseña del panel ---
$cfg = require __DIR__ . '/stats/config.php';
$key = $_GET['key'] ?? '';
if (!is_array($cfg) || !isset($cfg['password']) || !hash_equals((string)$cfg['password'], (string)$key)) {
    http_response_code(403);
    die("Acceso denegado. Agrega ?key=TU_CONTRASENA (la misma del panel de estadísticas).");
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== WERITA 3D — SELFTEST ===\n\n";
echo "PHP version:        " . PHP_VERSION . "\n";
echo "Timezone CDMX ok:   " . (in_array('America/Mexico_City', DateTimeZone::listIdentifiers()) ? 'SI' : 'NO') . "\n";
echo "Tu IP vista por PHP: " . werita_client_ip() . "\n\n";

// --- permisos de la carpeta stats ---
$dir = $WERITA_STATS_DIR;
echo "--- Carpeta stats/ ---\n";
echo "Ruta:               $dir\n";
echo "Existe:             " . (is_dir($dir) ? 'SI' : 'NO') . "\n";
echo "Escrible (PHP):     " . (is_writable($dir) ? "SI" : "NO - revisa permisos chown/chmod") . "\n\n";

// --- prueba de escritura directa ---
echo "--- Prueba de escritura ---\n";
$testFile = $dir . '/_selftest_write.tmp';
$ok = @file_put_contents($testFile, 'ok ' . date('c'));
if ($ok === false) {
    $err = error_get_last();
    echo "FALLO escribiendo archivo: " . ($err['message'] ?? 'error desconocido') . "\n";
    echo "<-- PROBLEMA de permisos. Solucion en tu servidor:\n";
    echo "    sudo chown -R www-data:www-data $dir\n";
    echo "    sudo chmod -R 775 $dir\n\n";
} else {
    @unlink($testFile);
    echo "OK: PHP puede crear/borrar archivos en stats/\n\n";
}

// --- prueba de registrar una visita ---
echo "--- Prueba registrar visita ---\n";
$r = werita_record_visit();
echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// --- prueba de registrar un click ---
echo "--- Prueba registrar click ---\n";
$r2 = werita_record_click('selftest');
echo json_encode($r2, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// --- últimos registros ---
foreach ([$WERITA_VISITS_FILE => 'visits.txt', $WERITA_CLICKS_FILE => 'clicks.txt'] as $f => $name) {
    echo "--- Últimas 5 líneas de $name ---\n";
    if (!file_exists($f)) { echo "(no existe aún)\n\n"; continue; }
    $lines = file($f);
    echo implode('', array_slice($lines, -5));
    echo "\n";
}

echo "=== FIN DEL SELFTEST ===\n";
echo "Si todo dijo OK pero el sitio no cuenta visitas, revisa que index.php\n";
echo "sea el archivo que realmente sirve Apache/Nginx (DocumentRoot) y que\n";
echo "el sitio usa PHP (no HTML estático cacheado).\n";
