# Werita 3D — Página Coming Soon 🚀

Página de "Muy Pronto" (Coming Soon) para **Werita 3D**, negocio de impresión 3D personalizada. Incluye logo, cuenta regresiva, enlaces a redes sociales y un diseño moderno con gradientes animados y partículas.

## ✨ Características

- Diseño responsive (móvil, tablet y escritorio)
- Fondo con gradiente animado y partículas decorativas
- Logo de Werita 3D
- Cuenta regresiva para el lanzamiento
- Botones de redes sociales con efectos hover
- 100% estático: HTML, CSS y JavaScript en un solo archivo (`index.html`)
- Sin dependencias ni frameworks ni build

## 📁 Estructura del proyecto

```
.
├── index.html        # Página coming soon (HTML + CSS + JS)
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

## 👀 Vista previa local

Opción 1 — Abrir directamente:

```bash
# Simplemente abre index.html en tu navegador
```

Opción 2 — Servidor local con Python:

```bash
python3 -m http.server 8000
# Abre http://localhost:8000
```

Opción 3 — Servidor local con Node.js:

```bash
npx serve .
```

## 🌐 Despliegue

Al ser un sitio 100% estático, puedes subirlo a cualquier hosting:

- **GitHub Pages**: sube el repo y actívalo en *Settings → Pages*
- **Netlify / Vercel**: arrastra la carpeta o conecta el repositorio
- **cPanel / FTP**: sube `index.html` y la carpeta `img/` a `public_html`

## 🛠️ Personalización

- **Fecha de lanzamiento**: edita la constante de la cuenta regresiva en el bloque `<script>` de `index.html`.
- **Colores**: modifica las variables CSS en `:root` dentro de `<style>` (`--accent`, `--accent-2`, etc.).
- **Logo**: reemplaza `img/logoWerita.png` manteniendo el mismo nombre de archivo.

## 📄 Licencia

© Werita 3D — Todos los derechos reservados.
