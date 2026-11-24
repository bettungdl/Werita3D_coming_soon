#!/usr/bin/env python3
"""
Werita 3D — Servidor de página "Coming Soon" con contador de estadísticas.

Registra en archivos .txt (carpeta stats/):
  - Cada visita a la página (visits.txt)
  - Cada clic en cada enlace (clicks.txt)
  - Conteos totales (counters.json)

Cada registro incluye fecha y hora exactas de la Ciudad de México
(America/Mexico_City, con horario de verano incluido) y la IP del visitante.

Uso:
    python3 stats-server.py            # puerto 8000 por defecto
    python3 stats-server.py 8080       # puerto personalizado

Estadísticas (solo visible para localhost):
    http://localhost:8000/stats        # resumen HTML
    http://localhost:8000/stats/raw    # datos crudos en JSON
"""

import json
import os
import sys
from datetime import datetime, timezone, timedelta
from http.server import ThreadingHTTPServer, SimpleHTTPRequestHandler
from urllib.parse import urlparse

try:
    from zoneinfo import ZoneInfo  # Python 3.9+
    CDMX = ZoneInfo("America/Mexico_City")
except Exception:  # fallback: CST fijo (UTC-6, sin horario de verano)
    CDMX = timezone(timedelta(hours=-6), name="CST (CDMX)")

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
STATS_DIR = os.path.join(BASE_DIR, "stats")
VISITS_FILE = os.path.join(STATS_DIR, "visits.txt")
CLICKS_FILE = os.path.join(STATS_DIR, "clicks.txt")
COUNTERS_FILE = os.path.join(STATS_DIR, "counters.json")

os.makedirs(STATS_DIR, exist_ok=True)


def now_cdmx():
    """Fecha y hora exacta de la Ciudad de México."""
    return datetime.now(timezone.utc).astimezone(CDMX)


def client_ip(handler):
    """Obtiene la IP real del visitante (soporta proxies tipo Cloudflare)."""
    for header in ("CF-Connecting-IP", "X-Forwarded-For", "X-Real-IP"):
        value = handler.headers.get(header)
        if value:
            return value.split(",")[0].strip()
    return handler.client_address[0]


def load_counters():
    try:
        with open(COUNTERS_FILE, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        return {"visits": 0, "clicks": {}}


def save_counters(counters):
    tmp = COUNTERS_FILE + ".tmp"
    with open(tmp, "w", encoding="utf-8") as f:
        json.dump(counters, f, ensure_ascii=False, indent=2)
    os.replace(tmp, COUNTERS_FILE)


class StatsHandler(SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=BASE_DIR, **kwargs)

    def log_message(self, fmt, *args):
        pass  # silenciar log consola; todo queda en los .txt

    def _json_response(self, obj, code=200):
        body = json.dumps(obj, ensure_ascii=False).encode("utf-8")
        self.send_response(code)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.send_header("Cache-Control", "no-store")
        self.end_headers()
        self.wfile.write(body)

    def _is_local(self):
        ip = client_ip(self)
        return ip in ("127.0.0.1", "::1", "localhost") or ip.startswith("127.")

    def do_GET(self):
        path = urlparse(self.path).path

        if path == "/api/visit":
            ts = now_cdmx().strftime("%Y-%m-%d %H:%M:%S %Z")
            ip = client_ip(self)
            ua = self.headers.get("User-Agent", "?")[:200]
            counters = load_counters()
            counters["visits"] += 1
            save_counters(counters)
            with open(VISITS_FILE, "a", encoding="utf-8") as f:
                f.write(f"{ts} | IP: {ip} | visitas totales: {counters['visits']} | UA: {ua}\n")
            print(f"[VISITA ] {ts} | IP: {ip} | total: {counters['visits']}")
            return self._json_response({"ok": True, "visits": counters["visits"]})

        if path.startswith("/api/click/"):
            link = path.rsplit("/", 1)[-1] or "unknown"
            ts = now_cdmx().strftime("%Y-%m-%d %H:%M:%S %Z")
            ip = client_ip(self)
            counters = load_counters()
            counters["clicks"][link] = counters["clicks"].get(link, 0) + 1
            total = sum(counters["clicks"].values())
            save_counters(counters)
            with open(CLICKS_FILE, "a", encoding="utf-8") as f:
                f.write(f"{ts} | IP: {ip} | link: {link} | clics de '{link}': {counters['clicks'][link]}\n")
            print(f"[CLIC   ] {ts} | IP: {ip} | link: {link}")
            return self._json_response({"ok": True, "link": link, "clicks": counters["clicks"][link]})

        if path in ("/stats", "/stats/", "/stats/index.html"):
            if not self._is_local():
                return self._json_response({"error": "Solo accesible desde localhost"}, 403)
            c = load_counters()
            rows_links = "".join(
                f"<tr><td>{k}</td><td>{v}</td></tr>" for k, v in sorted(c["clicks"].items(), key=lambda x: -x[1])
            ) or "<tr><td colspan='2'>Aún no hay clics registrados</td></tr>"
            try:
                with open(VISITS_FILE, "r", encoding="utf-8") as f:
                    last_visits = [l.strip() for l in f if l.strip()][-10:]
            except FileNotFoundError:
                last_visits = []
            rows_visits = "".join(f"<tr><td>{v}</td></tr>" for v in reversed(last_visits)) or \
                "<tr><td>Sin visitas aún</td></tr>"
            html = f"""<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<title>Stats — Werita 3D</title><style>
body{{font-family:sans-serif;background:#0f0c29;color:#fff;padding:2rem;max-width:900px;margin:auto}}
h1{{color:#8f6bff}} h2{{margin-top:2rem;border-bottom:1px solid #333;padding-bottom:.3rem}}
table{{width:100%;border-collapse:collapse;margin-top:.5rem;font-size:.85rem}}
td,th{{border:1px solid #333;padding:.4rem .6rem;text-align:left}}
.big{{font-size:2.5rem;font-weight:bold;color:#ff6bd5}}
code{{background:#222;padding:.1rem .3rem;border-radius:4px}}</style></head><body>
<h1>📊 Estadísticas — Werita 3D</h1>
<p>Hora mostrada: Ciudad de México (CDMX)</p>
<h2>Total de visitas</h2><div class="big">{c['visits']}</div>
<h2>Clics por enlace</h2><table><tr><th>Enlace</th><th>Clics</th></tr>{rows_links}</table>
<h2>Últimas 10 visitas</h2><table><tr><th>Fecha hora | IP | total | navegador</th></tr>{rows_visits}</table>
<p style="margin-top:2rem;color:#888">Archivos: <code>stats/visits.txt</code>, <code>stats/clicks.txt</code>, <code>stats/counters.json</code> · JSON: <a style="color:#8f6bff" href="/stats/raw">/stats/raw</a></p>
</body></html>"""
            body = html.encode("utf-8")
            self.send_response(200)
            self.send_header("Content-Type", "text/html; charset=utf-8")
            self.send_header("Content-Length", str(len(body)))
            self.send_header("Cache-Control", "no-store")
            self.end_headers()
            return self.wfile.write(body)

        if path == "/stats/raw":
            if not self._is_local():
                return self._json_response({"error": "Solo accesible desde localhost"}, 403)
            return self._json_response(load_counters())

        return super().do_GET()


def main():
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 8000
    server = ThreadingHTTPServer(("0.0.0.0", port), StatsHandler)
    print("=" * 60)
    print("  🖨️  Werita 3D — servidor coming soon con estadísticas")
    print(f"  Página:       http://localhost:{port}/")
    print(f"  Estadísticas: http://localhost:{port}/stats  (solo localhost)")
    print(f"  Archivos en:  {STATS_DIR}/")
    print("  Ctrl+C para detener")
    print("=" * 60)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nServidor detenido.")


if __name__ == "__main__":
    main()
