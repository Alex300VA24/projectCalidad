# SIGI Calidad

Plataforma Laravel para gestión documental de calidad (mapa de procesos, formatos oficiales, documentos).

## Requisitos

- **Opción Docker (recomendada):** Docker Desktop 4.x+ (con Docker Compose v2).
- **Opción manual:** PHP 8.3+, Composer 2, Node.js 22+, MySQL 8.

## Instalación con Docker (recomendada)

1. Copiar el archivo de entorno:

   ```bash
   cp .env.example .env
   ```

2. En `.env`, establecer una contraseña no vacía para la base de datos:

   ```
   DB_PASSWORD=una_clave_segura
   ```

3. Levantar los contenedores (construye la imagen la primera vez):

   ```bash
   docker compose up -d --build
   ```

   Esto crea dos servicios:
   - `app`: PHP-FPM + Nginx (imagen `sigi-calidad-app`), sirve la app en `http://localhost:8000`.
   - `db`: MySQL 8.4 con datos persistentes en el volumen `db-data`.

   Al iniciar, el contenedor `app` ejecuta automáticamente: generación de `APP_KEY` (si falta), `storage:link`, migraciones (`migrate --force`) y cacheo de config/rutas/vistas.

4. Abrir `http://localhost:8000`.

### Puertos ocupados

Si el puerto 8000 (app) o 3307 (MySQL expuesto al host) ya están en uso por otro proyecto, sobreescribir antes de levantar:

```bash
APP_PORT=8080 DB_FORWARD_PORT=3308 docker compose up -d --build
```

O definir `APP_PORT` y `DB_FORWARD_PORT` directamente en `.env`.

### Volúmenes montados

- `./storage` → persistencia de logs, cache de vistas, archivos subidos.
- `./database/data` → JSON locales de datos (ej. `formatos-llenados.json`), ignorados por git.
- `./DOCS` (solo lectura) → repositorio real de formatos/documentos oficiales que sirve `FormatoOficialController`.

### Comandos útiles

```bash
docker compose logs -f app          # ver logs
docker compose exec app php artisan tinker
docker compose down                 # detener (conserva datos)
docker compose down -v              # detener y borrar volumen de MySQL
```

## Instalación manual (sin Docker)

1. Instalar dependencias:

   ```bash
   composer install
   npm install
   ```

2. Configurar entorno:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Ajustar en `.env` las credenciales de MySQL local (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

3. Migrar base de datos y enlazar storage:

   ```bash
   php artisan migrate
   php artisan storage:link
   ```

4. Construir assets y levantar el servidor de desarrollo:

   ```bash
   composer run dev
   ```

   (Corre `php artisan serve`, cola, logs y `vite dev` en paralelo. Alternativa: `npm run build` + `php artisan serve`.)

## Notas

- Las rutas de la app son públicas, sin autenticación.
- La vista previa de formatos `.docx`/`.xlsx` en el modal usa el visor de Microsoft Office Online (`view.officeapps.live.com`), que necesita que la URL de la app sea alcanzable desde internet público. En `localhost`/intranet pura la previsualización de esos formatos no renderiza (la descarga sigue funcionando siempre); los PDF sí se previsualizan de forma nativa en cualquier entorno.
- La carpeta `DOCS/` contiene los formatos y documentos oficiales reales; está versionada en git (no ignorada).
