# UniFood - Tienda Universitaria (Universidad del Valle)

Sistema web para pedidos de comida universitaria desarrollado en Laravel (Blade puro y Bootstrap 5 por CDN).

## Cómo levantar el proyecto
1. `php artisan migrate:fresh --seed`
2. `php artisan serve`
3. Ingresar a `http://localhost:8000`

## Credenciales de Acceso (Contraseña común: `password`)
- **Administrador**: `admin@unifood.bo` (Gestión de productos y panel de pedidos)
- **Empleado**: `empleado@unifood.bo` (Panel de preparación y entrega de pedidos)
- **Estudiante**: `estudiante@unifood.bo` (Catálogo, carrito de compras y mis pedidos)

## Recuperación de Contraseña
- Correo configurado con driver `log`. Los enlaces de reseteo se registran en `storage/logs/laravel.log`.
