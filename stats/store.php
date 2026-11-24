<?php
/**
 * Werita 3D — Almacenamiento de estadísticas en archivos .txt / .json.
 *
 * Registros con fecha y hora exacta de la Ciudad de México (America/Mexico_City)
 * e IP del visitante.
 */

// ===== Rutas de archivos =====
$WERITA_STATS_DIR      = __DIR__;                          // carpeta stats/
$WERITA_VISITS_FILE    = $WERITA_STATS_DIR . '/visits.txt';
$WERITA_CLICKS_FILE    = $WERITA_STATS_DIR . '/clicks.txt';
$WERITA_COUNTERS_FILE  = $WERITA_STATS_DIR . '/counters.json';
$WERITA_LASTVISIT_FILE = $WERITA_STATS_DIR . '/last_visit.json';

date_default_timezone_set('America/Mexico_City');           // hora de CDMX

/**
 * Fecha y hora exacta de la Ciudad de México.
 */
function werita_now() {
    return new DateTime('now', new DateTimeZone('America/Mexico_City'));
}

function werita_timestamp() {
    $now = werita_now();
    return $now->format('Y-m-d H:i:s') . ' ' . $now->getTimezone()->getName();
}

/**
 * IP real del visitante (soporta proxies tipo Cloudflare).
 */
function werita_client_ip() {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Carga los contadores acumulados (o valores iniciales).
 */
function werita_load_counters() {
    global $WERITA_COUNTERS_FILE;
    if (is_readable($WERITA_COUNTERS_FILE)) {
        $data = json_decode(file_get_contents($WERITA_COUNTERS_FILE), true);
        if (is_array($data)) {
            return $data + ['visits' => 0, 'clicks' => []];
        }
    }
    return ['visits' => 0, 'clicks' => []];
}

function werita_save_counters(array $counters) {
    global $WERITA_COUNTERS_FILE;
    $tmp = $WERITA_COUNTERS_FILE . '.tmp';
    file_put_contents($tmp, json_encode($counters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    rename($tmp, $WERITA_COUNTERS_FILE);
}

/**
 * Registra una visita (sin throttle). Devuelve array para respuesta JSON.
 */
function werita_record_visit() {
    global $WERITA_VISITS_FILE;
    $ts  = werita_timestamp();
    $ip  = werita_client_ip();
    $ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '?', 0, 200);
    $ref = $_SERVER['HTTP_REFERER'] ?? '-';

    $counters = werita_load_counters();
    $counters['visits']++;
    werita_save_counters($counters);

    file_put_contents(
        $WERITA_VISITS_FILE,
        sprintf("%s | IP: %s | visitas totales: %d | UA: %s | ref: %s\n", $ts, $ip, $counters['visits'], $ua, $ref),
        FILE_APPEND | LOCK_EX
    );

    return ['ok' => true, 'visits' => $counters['visits']];
}

/**
 * Registra una visita solo si pasaron más de $seconds desde la última
 * visita de esta misma IP (evita inflar el contador con refreshes).
 */
function werita_record_visit_throttled($seconds = 1800) {
    global $WERITA_LASTVISIT_FILE;
    $ip = werita_client_ip();

    $last = [];
    if (is_readable($WERITA_LASTVISIT_FILE)) {
        $last = json_decode(file_get_contents($WERITA_LASTVISIT_FILE), true) ?: [];
    }

    $now = time();
    if (isset($last[$ip]) && ($now - $last[$ip]) < $seconds) {
        return ['ok' => true, 'throttled' => true]; // visita reciente, no cuenta de nuevo
    }

    $last[$ip] = $now;
    // Limpia IPs inactivas hace más de 7 días para que el archivo no crezca sin fin
    foreach ($last as $k => $v) {
        if (($now - $v) > 7 * 86400) unset($last[$k]);
    }
    file_put_contents($WERITA_LASTVISIT_FILE, json_encode($last), LOCK_EX);

    return werita_record_visit();
}

/**
 * Registra un clic en un enlace.
 */
function werita_record_click($link) {
    global $WERITA_CLICKS_FILE;
    $ts = werita_timestamp();
    $ip = werita_client_ip();

    $counters = werita_load_counters();
    if (!isset($counters['clicks'][$link])) $counters['clicks'][$link] = 0;
    $counters['clicks'][$link]++;
    werita_save_counters($counters);

    file_put_contents(
        $WERITA_CLICKS_FILE,
        sprintf("%s | IP: %s | link: %s | clics de '%s': %d\n", $ts, $ip, $link, $link, $counters['clicks'][$link]),
        FILE_APPEND | LOCK_EX
    );

    return ['ok' => true, 'link' => $link, 'clicks' => $counters['clicks'][$link]];
}

/**
 * Últimas $n líneas de un archivo de log (más reciente primero).
 */
function werita_tail($file, $n = 10) {
    if (!is_readable($file)) return [];
    $lines = array_values(array_filter(array_map('trim', file($file)), function ($l) { return $l !== ''; }));
    return array_reverse(array_slice($lines, -$n));
}
