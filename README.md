# Werita 3D — Página Coming Soon 🚀

Página de "Muy Pronto" (Coming Soon) para **Werita 3D**, negocio de impresión 3D personalizada. Incluye logo, cuenta regresiva, enlaces a redes sociales y un diseño moderno con gradientes animados y partículas.

## ✨ Características

- Diseño responsive (móvil, tablet y escritorio)
- Fondo con gradiente animado y partículas decorativas
- Logo de Werita 3D
- Cuenta regresiva para el lanzamiento
- Botones de redes sociales con efectos hover
- 📊 Contador interno de estadísticas: visitas y clics por enlace, con fecha/hora de CDMX e IP (vía `stats-server.py`)
- Sin dependencias ni frameworks ni build (solo Python para el servidor de estadísticas)

## 📁 Estructura del proyecto

```
.
├── index.html        # Página coming soon (HTML + CSS + JS + tracking)
├── stats-server.py   # Servidor local que registra visitas y clics
├── stats/            # Se crea automáticamente al correr el servidor
│   ├── visits.txt    # Una línea por visita: fecha/hora CDMX | IP | total | navegador
│   ├── clicks.txt    # Una línea por clic: fecha/hora CDMX | IP | enlace
│   └── counters.json # Totales acumulados
├── img/
│   └── logoWerita.png  # Logo de Werita 3D
└── README.md
```

## 🔗 Redes sociales y enlaces

| Plataforma | Enlace |
|------------|--------|
| TikTok     | https://www.tiktok.com/@werita.3d |
| Instagram  | https://www.instagram.com/werita3d |
| Facebook   | https://www.facebook.com/profile.php?id=61597034874808 |
| Web        | https://werita3d.com |

> **Nota:** El enlace de Facebook usa temporalmente la URL con ID de perfil. Cuando se pueda configurar el nombre de usuario, se debe actualizar a `https://www.facebook.com/Werita3D`.

## 👀 Vista previa local (con estadísticas) ✅ Recomendada

```bash
python3 stats-server.py          # puerto 8000 por defecto
python3 stats-server.py 8080     # o un puerto personalizado
```

- Página: http://localhost:8000/
- Panel de estadísticas (solo accesible desde tu propia máquina): http://localhost:8000/stats
- Datos crudos en JSON: http://localhost:8000/stats/raw

Cada visita y cada clic se guarda en `stats/visits.txt` y `stats/clicks.txt` con la **fecha y hora exacta de la Ciudad de México** (incluye horario de verano), la **IP** del visitante y el navegador. Los totales acumulados están en `stats/counters.json`.

### Otras formas de ver la página (sin estadísticas)

```bash
# Abrir index.html directamente en el navegador, o:
python3 -m http.server 8000
npx serve .
```

Si la página se sirve sin `stats-server.py`, las peticiones de tracking fallan silenciosamente y todo lo demás funciona igual.

## 🌐 Despliegue

Al ser un sitio esencialmente estático, puedes subirlo a cualquier hosting:

- **GitHub Pages**: sube el repo y actívalo en *Settings → Pages*
- **Netlify / Vercel**: arrastra la carpeta o conecta el repositorio
- **cPanel / FTP**: sube `index.html` y la carpeta `img/` a `public_html`

> ⚠️ Nota: el contador de estadísticas requiere correr `stats-server.py` (Python) en el servidor. En hostings 100% estáticos (GitHub Pages, Netlify) no habrá registro; ahí conviene usar Google Analytics o similar.

## 🛠️ Personalización

- **Fecha de lanzamiento**: edita la constante de la cuenta regresiva en el bloque `<script>` de `index.html`.
- **Colores**: modifica las variables CSS en `:root` dentro de `<style>` (`--accent`, `--accent-2`, etc.).
- **Logo**: reemplaza `img/logoWerita.png` manteniendo el mismo nombre de archivo.

## 📄 Licencia

© Werita 3D — Todos los derechos reservados.
