# Werita 3D — Página Coming Soon 🚀

Página de "Muy Pronto" (Coming Soon) para **Werita 3D**, negocio de impresión 3D personalizada. Incluye logo, cuenta regresiva, enlaces a redes sociales, diseño moderno con gradientes animados y un **contador interno de estadísticas en PHP** (visitas y clics).

## ✨ Características

- Diseño responsive (móvil, tablet y escritorio)
- Fondo con gradiente animado y partículas decorativas
- Logo de Werita 3D (`img/logoWerita.png`)
- Cuenta regresiva para el lanzamiento
- Botones de redes sociales con efectos hover
- 📊 Estadísticas internas en PHP puro: visitas y clics por enlace, con **fecha/hora exacta de CDMX**, **IP** y navegador
- Panel de estadísticas protegido con contraseña
- Los datos se guardan en archivos `.txt` / `.json` (fácil de revisar o respaldar)

## 📁 Estructura del proyecto

```
.
├── index.php           # Página coming soon + API de tracking (PHP)
├── stats/
│   ├── store.php       # Lógica de registro (hora CDMX, IP, archivos .txt)
│   ├── view.php        # Panel de estadísticas (con login)
│   ├── config.php      # ⚠️ Contraseña del panel (cámbiala aquí)
│   ├── .htaccess       # Bloquea acceso directo a los .txt/.json (Apache)
│   ├── visits.txt      # Se crea solo: una línea por visita
│   ├── clicks.txt      # Se crea solo: una línea por clic
│   └── counters.json   # Se crea solo: totales acumulados
├── img/
│   └── logoWerita.png  # Logo de Werita 3D
├── .gitignore          # Ignora los datos generados en stats/
└── README.md
```

## 🔗 Redes sociales y enlaces

| Plataforma | Enlace |
|------------|--------|
| TikTok     | https://www.tiktok.com/@werita.3d |
| Instagram  | https://www.instagram.com/werita3d |
| Facebook   | https://www.facebook.com/profile.php?id=61597034874808 |
| Web        | https://werita3d.com |

> **Nota:** El enlace de Facebook usa temporalmente la URL con ID de perfil porque aún no tiene nombre de usuario. Cuando esté disponible, actualizar a `https://www.facebook.com/Werita3D` en `index.php`.

## 👀 Cómo ver la página localmente (con PHP)

Necesitas PHP instalado. Opciones rápidas:

```bash
# Opción 1: servidor embebido de PHP (la más fácil)
php -S localhost:8000

# Opción 2: si usas XAMPP / MAMP / Laragon
# Copia la carpeta del proyecto a htdocs/ (o www/) y abre http://localhost/tu-carpeta/
```

Luego abre en tu navegador:

- **Página:** http://localhost:8000/
- **Panel de estadísticas:** http://localhost:8000/index.php?stats=1
  - Contraseña por defecto: `werita2026` → **cámbiala en `stats/config.php`**

### ¿Cómo funciona el conteo?

- **Visitas:** se registran automáticamente al cargar la página (lado del servidor, funciona aunque el visitante tenga JavaScript desactivado). Para no inflar el número con refrescos, la misma IP solo cuenta **1 visita cada 30 minutos**.
- **Clics:** cada botón de red social dispara un registro cuando alguien hace clic (JavaScript).
- Cada línea en `stats/visits.txt` y `stats/clicks.txt` incluye: fecha y hora de CDMX, IP, y detalles (navegador/referencia o enlace). Los totales están en `stats/counters.json`.

Ejemplo de `stats/visits.txt`:

```
2026-11-24 13:31:57 America/Mexico_City | IP: 201.22.33.44 | visitas totales: 1 | UA: Mozilla/5.0 ... | ref: -
```

## 🌐 Despliegue a tu sitio

Este proyecto está pensado para cualquier hosting con PHP (prácticamente todos: cPanel, Hostinger, Namecheap, SiteGround, etc.):

1. Sube el contenido del repo a `public_html/` (o la raíz de tu dominio): `index.php`, `img/` y `stats/`.
2. Edita la contraseña en `stats/config.php`.
3. Verifica que el servidor web pueda **escribir** dentro de `stats/` (permisos `755` o `775` en la carpeta).
4. Visita tu dominio; la primera carga ya debería aparecer en `stats/visits.txt`.

> ⚠️ No sirve en hostings 100% estáticos (GitHub Pages, Netlify free sin funciones) porque necesita PHP.

## 🛠️ Personalización

- **Contraseña del panel:** `stats/config.php` → variable `$WERITA_STATS_PASSWORD`.
- **Anti-refresco (throttle de visitas):** cambia `30 * 60` (segundos) en `index.php`, línea de `werita_record_visit_throttled()`.
- **Fecha de lanzamiento:** edita la constante de la cuenta regresiva en el bloque `<script>` de `index.php`.
- **Colores:** variables CSS en `:root` (`--accent`, `--accent-2`, etc.).
- **Logo:** reemplaza `img/logoWerita.png` manteniendo el mismo nombre.
- **Enlace de Facebook:** actualízalo en `index.php` cuando el perfil tenga nombre de usuario.

## 📄 Licencia

© Werita 3D — Todos los derechos reservados.
