# UniFood: primera etapa móvil (PWA)

La interfaz Blade existente se comparte entre web y móvil. Esta etapa incorpora manifiesto, iconos PNG de 192/512 px, icono maskable y Apple Touch, instalación cuando el navegador la ofrece, ayuda para iOS y una pantalla sin conexión.

## Preparación

1. Instalar PHP compatible con composer.lock (el proyecto declara PHP ^8.3, pero sus dependencias bloqueadas exigen PHP >=8.4.1), Composer y Node compatible con Vite 8.
2. Ejecutar `composer install` y `npm ci` en la raíz del proyecto.
3. Si no existe `.env`, copiar `.env.example`, configurar la conexión a una base de datos de desarrollo y ejecutar `php artisan key:generate`.
4. Ejecutar `php artisan migrate --seed` en esa base de desarrollo. No usar migrate:fresh sobre datos que se quieran conservar.
5. Ejecutar `npm run build` y `php artisan serve`.
6. Abrir `http://localhost:8000`. En Chrome/Edge, el botón «Instalar UniFood» aparece cuando el navegador ofrece la instalación. También puede usarse su menú.

En un celular, localhost apunta al propio teléfono. Acceder por una IP HTTP de la PC permite ver la web pero no ofrece el contexto seguro necesario para probar el service worker y la instalación completa. Para esa prueba usar un alojamiento HTTPS o un entorno local con HTTPS y certificado confiable para el teléfono. No se ha publicado el proyecto ni configurado ese alojamiento.

En iPhone/iPad abrir en Safari, Compartir → Agregar a inicio. Las opciones exactas varían según navegador y versión.

## Conexión y privacidad

Se requiere internet para iniciar sesión, ver catálogo, consultar carrito/pedidos y confirmar o cambiar pedidos. No se guardan páginas autenticadas, respuestas API ni solicitudes POST/PUT/DELETE en Cache Storage. Solo hay una lista explícita de recursos públicos. Si una navegación falla, se muestra una pantalla genérica sin datos personales. Esa pantalla no confirma una compra: consultar Mis pedidos antes de repetir un envío interrumpido.

No hay sincronización de pedidos sin conexión, notificaciones push ni APK en esta etapa. El funcionamiento sin conexión requiere al menos una visita previa que haya instalado el worker correctamente.

`public/sw.js` debe servirse desde el mismo origen que la aplicación, en la raíz de su alcance. Evitar un ASSET_URL de CDN para estos recursos. Incrementar CACHE_NAME cuando se cambien los recursos precargados. Las actualizaciones esperan al cierre de las pestañas controladas por la versión anterior.

## Comprobación

- `npm run test:pwa`: pruebas automatizadas de aislamiento del caché, navegación, fallos de red y actualización del worker. Son pruebas de comportamiento con entorno simulado; no certifican instalación en un teléfono.
- `npm run build`: compilación del frontend.
- `php artisan test`: regresión de Laravel; requiere dependencias y PHP compatible.

| Caso | Procedimiento | Resultado esperado |
| --- | --- | --- |
| PWA-01 | DevTools → Application → Manifest | Nombre, iconos y alcance válidos; sin errores |
| PWA-02 | Instalar en Android con HTTPS | Icono y apertura en ventana independiente |
| PWA-03 | Agregar a inicio en iOS | Nombre/icono correctos y apertura usable |
| PWA-04 | Visitar online, esperar worker activo, desconectar y navegar a una URL de pedido | Pantalla genérica sin conexión; sin datos privados |
| PWA-05 | Desconectar durante una operación y reconectar | Sin reenvío automático; verificar estado en Mis pedidos |
| PWA-06 | Application → Cache Storage tras usar cuentas diferentes | Solo recursos públicos; ningún HTML autenticado |
| PWA-07 | Anchos de 360 y 390 px: login, catálogo, carrito y pedidos | Menú operable, botones accesibles, sin desbordamiento |
| PWA-08 | Flujo estudiante → pedido → empleado → entregado | Regresión funcional sin pérdida de permisos |
| PWA-09 | Cerrar sesión, cambiar cuenta y abrir un pedido ajeno | Acceso rechazado por el servidor |

Registrar dispositivo, navegador, fecha, resultado y captura por caso. Los casos manuales siguen pendientes hasta ejecutarlos en la aplicación real.

Referencias técnicas: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Making_PWAs_installable y https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API/Using_Service_Workers
