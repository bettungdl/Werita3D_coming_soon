<?php
/**
 * Werita 3D — Página Coming Soon con contador interno de estadísticas (PHP).
 *
 * Registra en archivos .txt dentro de stats/:
 *   - visits.txt   : una línea por visita (fecha/hora CDMX | IP | navegador)
 *   - clicks.txt   : una línea por clic (fecha/hora CDMX | IP | enlace)
 *   - counters.json: totales acumulados
 *
 * Panel de estadísticas: index.php?stats=1 (contraseña en stats/config.php)
 */

require_once __DIR__ . '/stats/store.php';

$action = $_GET['api'] ?? null;

// ===== API de tracking (llamada desde JS con fetch/sendBeacon) =====
if ($action === 'visit') {
    $result = werita_record_visit();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}
if ($action === 'click') {
    $link = preg_replace('/[^a-z0-9\-_]/i', '', $_GET['link'] ?? 'unknown');
    $result = werita_record_click($link !== '' ? $link : 'unknown');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}

// ===== Panel de estadísticas =====
if (isset($_GET['stats'])) {
    require __DIR__ . '/stats/view.php';
    exit;
}

// ===== Registro server-side de la visita (funciona incluso sin JS) =====
// Para no inflar el contador con refreshes, solo cuenta como visita nueva
// si pasó más de 30 minutos desde la última vez que esta sesión/IP visitó.
werita_record_visit_throttled(30 * 60);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Werita 3D — Muy Pronto</title>
  <meta name="description" content="Werita 3D — Impresión 3D personalizada. ¡Muy pronto! Síguenos en nuestras redes sociales." />
  <link rel="icon" type="image/png" href="img/logoWerita.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg-1: #0f0c29;
      --bg-2: #302b63;
      --bg-3: #24243e;
      --accent: #8f6bff;
      --accent-2: #ff6bd5;
      --text: #ffffff;
      --muted: rgba(255, 255, 255, 0.7);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text);
      background: linear-gradient(-45deg, var(--bg-1), var(--bg-2), var(--bg-3), #1a1440);
      background-size: 400% 400%;
      animation: gradientMove 15s ease infinite;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow-x: hidden;
      position: relative;
    }

    @keyframes gradientMove {
      0%   { background-position: 0% 50%; }
      50%  { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Partículas decorativas de fondo */
    .particles {
      position: fixed;
      inset: 0;
      pointer-events: none;
      z-index: 0;
    }
    .particle {
      position: absolute;
      bottom: -20px;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(143, 107, 255, 0.5);
      animation: floatUp linear infinite;
    }
    @keyframes floatUp {
      0%   { transform: translateY(0) scale(1); opacity: 0; }
      10%  { opacity: 1; }
      100% { transform: translateY(-110vh) scale(0.3); opacity: 0; }
    }

    main {
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 3rem 1.5rem;
      max-width: 720px;
      width: 100%;
    }

    .logo-wrap {
      display: inline-block;
      margin-bottom: 2rem;
      animation: fadeDown 1s ease both;
    }
    .logo-wrap img {
      width: clamp(160px, 35vw, 260px);
      height: auto;
      filter: drop-shadow(0 0 30px rgba(143, 107, 255, 0.6));
      transition: transform 0.4s ease;
    }
    .logo-wrap img:hover { transform: scale(1.05) rotate(-2deg); }

    h1 {
      font-size: clamp(2.2rem, 6vw, 3.8rem);
      font-weight: 800;
      letter-spacing: 1px;
      background: linear-gradient(90deg, var(--accent), var(--accent-2));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin-bottom: 0.75rem;
      animation: fadeUp 1s ease 0.2s both;
    }

    .subtitle {
      font-size: clamp(1rem, 2.5vw, 1.25rem);
      font-weight: 300;
      color: var(--muted);
      margin-bottom: 2.5rem;
      animation: fadeUp 1s ease 0.4s both;
    }

    /* Contador regresivo */
    .countdown {
      display: flex;
      justify-content: center;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 3rem;
      animation: fadeUp 1s ease 0.6s both;
    }
    .count-box {
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 1rem 1.25rem;
      min-width: 90px;
      transition: transform 0.3s ease, border-color 0.3s ease;
    }
    .count-box:hover {
      transform: translateY(-5px);
      border-color: var(--accent);
    }
    .count-box .number {
      font-size: clamp(1.8rem, 5vw, 2.6rem);
      font-weight: 700;
      display: block;
    }
    .count-box .label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: var(--muted);
    }

    /* Redes sociales */
    .social-title {
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 3px;
      color: var(--muted);
      margin-bottom: 1.25rem;
      animation: fadeUp 1s ease 0.8s both;
    }
    .socials {
      display: flex;
      justify-content: center;
      gap: 1.25rem;
      flex-wrap: wrap;
      animation: fadeUp 1s ease 1s both;
    }
    .social-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--text);
      text-decoration: none;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 50px;
      padding: 0.7rem 1.4rem;
      font-size: 0.95rem;
      font-weight: 400;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
    }
    .social-btn svg { width: 20px; height: 20px; fill: currentColor; }
    .social-btn:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px rgba(143, 107, 255, 0.35);
    }
    .social-btn.tiktok:hover { background: #010101; border-color: #010101; }
    .social-btn.instagram:hover {
      background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
      border-color: transparent;
    }
    .social-btn.facebook:hover { background: #1877f2; border-color: #1877f2; }
    .social-btn.web:hover { background: linear-gradient(90deg, var(--accent), var(--accent-2)); border-color: transparent; }

    footer {
      margin-top: 3rem;
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.4);
      animation: fadeUp 1s ease 1.2s both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-30px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 480px) {
      .count-box { min-width: 72px; padding: 0.75rem; }
      .social-btn { padding: 0.6rem 1.1rem; font-size: 0.85rem; }
    }
  </style>
</head>
<body>
  <div class="particles" id="particles"></div>

  <main>
    <div class="logo-wrap">
      <img src="img/logoWerita.png" alt="Logo de Werita 3D" />
    </div>

    <h1>¡Muy Pronto!</h1>
    <p class="subtitle">Estamos creando algo increíble en <strong>3D</strong>.<br />Vuelve pronto para descubrirlo. 🚀</p>

    <div class="countdown" id="countdown" aria-label="Cuenta regresiva">
      <div class="count-box"><span class="number" id="days">--</span><span class="label">Días</span></div>
      <div class="count-box"><span class="number" id="hours">--</span><span class="label">Horas</span></div>
      <div class="count-box"><span class="number" id="minutes">--</span><span class="label">Minutos</span></div>
      <div class="count-box"><span class="number" id="seconds">--</span><span class="label">Segundos</span></div>
    </div>

    <p class="social-title">Síguenos</p>
    <div class="socials">
      <a class="social-btn tiktok" data-track="tiktok" href="https://www.tiktok.com/@werita.3d" target="_blank" rel="noopener noreferrer" aria-label="TikTok de Werita 3D">
        <svg viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.3 0 .58.05.85.13V9.4a6.33 6.33 0 0 0-1-.05A6.34 6.34 0 0 0 7.9 22a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-3.94-2z"/></svg>
        TikTok
      </a>
      <a class="social-btn instagram" data-track="instagram" href="https://www.instagram.com/werita3d" target="_blank" rel="noopener noreferrer" aria-label="Instagram de Werita 3D">
        <svg viewBox="0 0 24 24"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.64.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.64-.07-4.85s.01-3.58.07-4.85C2.38 3.92 3.9 2.38 7.15 2.23 8.42 2.18 8.8 2.16 12 2.16zm0 3.68a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-10.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/></svg>
        Instagram
      </a>
      <a class="social-btn facebook" data-track="facebook" href="https://www.facebook.com/profile.php?id=61597034874808" target="_blank" rel="noopener noreferrer" aria-label="Facebook de Werita 3D">
        <svg viewBox="0 0 24 24"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
        Facebook
      </a>
      <a class="social-btn web" data-track="web-werita3d-com" href="https://werita3d.com" target="_blank" rel="noopener noreferrer" aria-label="Sitio web de Werita 3D">
        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm7.93 9h-3.02a15.7 15.7 0 0 0-1.2-5.4A8.03 8.03 0 0 1 19.93 11zM12 4c.9 1.2 1.87 3.6 2.06 7H9.94C10.13 7.6 11.1 5.2 12 4zM4.07 13h3.02c.08 1.9.5 3.77 1.2 5.4A8.03 8.03 0 0 1 4.07 13zm3.02-2H4.07a8.03 8.03 0 0 1 4.22-5.4A15.7 15.7 0 0 0 7.09 11zM12 20c-.9-1.2-1.87-3.6-2.06-7h4.12c-.19 3.4-1.16 5.8-2.06 7zm3.71-1.6c.7-1.63 1.12-3.5 1.2-5.4h3.02a8.03 8.03 0 0 1-4.22 5.4z"/></svg>
        Werita3D.com
      </a>
    </div>

    <footer>© <span id="year"></span> Werita 3D — Todos los derechos reservados</footer>
  </main>

  <script>
    // Año dinámico
    document.getElementById('year').textContent = new Date().getFullYear();

    // Cuenta regresiva: 30 días desde hoy
    const launchDate = new Date();
    launchDate.setDate(launchDate.getDate() + 30);

    const els = {
      days: document.getElementById('days'),
      hours: document.getElementById('hours'),
      minutes: document.getElementById('minutes'),
      seconds: document.getElementById('seconds')
    };

    function updateCountdown() {
      const diff = launchDate - new Date();
      if (diff <= 0) {
        Object.values(els).forEach(el => el.textContent = '00');
        return;
      }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      els.days.textContent = String(d).padStart(2, '0');
      els.hours.textContent = String(h).padStart(2, '0');
      els.minutes.textContent = String(m).padStart(2, '0');
      els.seconds.textContent = String(s).padStart(2, '0');
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    // Partículas flotantes
    const particlesContainer = document.getElementById('particles');
    const colors = ['rgba(143,107,255,0.5)', 'rgba(255,107,213,0.4)', 'rgba(255,255,255,0.3)'];
    for (let i = 0; i < 25; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      const size = Math.random() * 8 + 4;
      p.style.width = size + 'px';
      p.style.height = size + 'px';
      p.style.left = Math.random() * 100 + '%';
      p.style.background = colors[Math.floor(Math.random() * colors.length)];
      p.style.animationDuration = (Math.random() * 15 + 10) + 's';
      p.style.animationDelay = (Math.random() * 15) + 's';
      particlesContainer.appendChild(p);
    }

    // ===== Estadísticas internas (clics) =====
    // La VISITA ya se registró del lado del servidor al cargar la página
    // (throttle de 30 min por IP). Aquí solo se registran los CLICS.
    const trackClick = (link) => {
      const url = 'index.php?api=click&link=' + encodeURIComponent(link);
      try {
        if (navigator.sendBeacon) navigator.sendBeacon(url);
        else fetch(url, { keepalive: true }).catch(() => {});
      } catch (e) { /* ignorar */ }
    };

    document.querySelectorAll('a[data-track]').forEach(link => {
      link.addEventListener('click', () => trackClick(link.dataset.track));
    });
  </script>
</body>
</html>
