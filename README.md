# UniFood - Tienda Universitaria (Universidad del Valle)

Sistema web para pedidos de comida universitaria desarrollado en Laravel (Blade puro y Bootstrap 5 por CDN).

## Cómo levantar el proyecto
Con PHP 8.4.1 o posterior, Composer y Node 22 o posterior, instalar las dependencias y configurar `.env` conforme a `.env.example`.

1. `composer install`
2. `npm ci`
3. `php artisan migrate --no-interaction`
4. `npm run build`
5. `php artisan serve`

Las migraciones conservan los datos existentes. En una instalación nueva, cargar las cuentas y productos de demostración con `php artisan db:seed --no-interaction`.

## Entrega SQA

- [Documentos y resultados de la entrega](docs/sqa/README.md).
- [Test Plan y actualización técnica](docs/sqa/Actualizacion_SQA_2026-10-28.md).
- [Tablero público de SQA y Scrum](https://github.com/users/ifrancro/projects/3).
- [Integración continua y artefactos](https://github.com/ifrancro/UniFood/actions/workflows/calidad.yml).
- [Guion de exposición para tres personas](docs/sqa/Guion_Exposicion_3_Personas.md).

## Credenciales de Acceso (Contraseña común: `password`)
- **Administrador**: `admin@unifood.bo` (Gestión de productos y panel de pedidos)
- **Empleado**: `empleado@unifood.bo` (Panel de preparación y entrega de pedidos)
- **Estudiante**: `estudiante@unifood.bo` (Catálogo, carrito de compras y mis pedidos)

## Recuperación de Contraseña
- Correo configurado con driver `log`. Los enlaces de reseteo se registran en `storage/logs/laravel.log`.

## Versión móvil PWA

Consultar [la guía de instalación y pruebas](docs/PWA.md). Pruebas del service worker: `npm run test:pwa`.
