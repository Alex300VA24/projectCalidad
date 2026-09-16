# SIGI Calidad

Sistema institucional de gestión de indicadores y evidencias construido con Laravel 13.

## Funcionalidades

- Tablero ejecutivo con cumplimiento global y semáforo de estados.
- Registro, búsqueda, actualización de avance y eliminación de indicadores.
- Repositorio documental organizado por secciones.
- Vinculación de PDFs públicos de Google Drive y visualización en un modal accesible.
- Diseño adaptable, modo oscuro y navegación por teclado.

## Puesta en marcha

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve
```

Abre `http://127.0.0.1:8000`.

## Documentos de Drive

Antes de registrar un PDF, en Google Drive selecciona **Compartir** y configura **Acceso general → Cualquier persona con el enlace → Lector**. Luego pega el enlace generado en el módulo Documentos.

Los cuatro documentos iniciales contienen identificadores demostrativos. Sustitúyelos desde la interfaz por los enlaces reales de la institución.

## Pruebas

```bash
php artisan test
```
