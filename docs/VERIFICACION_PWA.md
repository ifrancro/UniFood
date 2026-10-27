# UniFood: revisión del plan SQA e inicio de PWA

Fecha de revisión: 26 de octubre de 2026.

## Lo que pide el PDF

La actividad se titula «Actualización del Test Plan en un entorno ágil». El documento no pide explícitamente una PWA; la primera etapa móvil procede de tu solicitud.

Se debe describir el marco ágil real del equipo, identificar roles de calidad y artefactos del sprint, revisar el Test Plan previo y actualizarlo con estrategia continua, automatización con dos herramientas (menciona TestLink, Squash, Selenium y Cypress), TDD/BDD/ATDD, integración continua y priorización por valor.

Los ocho apartados mínimos del Test Plan son: introducción, alcance, estrategia de pruebas, roles y responsabilidades, ciclo de vida de pruebas, criterios de entrada/salida (Definition of Ready/Done), métricas y control de calidad, riesgos y mitigación.

Las evidencias incluyen el Test Plan actualizado, repositorio con pruebas, informe de auditoría interna con acciones correctivas, integración con un tablero ágil, resultados de pruebas e indicadores de calidad.

La evaluación asigna 25% a la actualización del plan, 20% a estrategias de prueba, 20% a evidencias de seguimiento, 15% a documento/presentación y 20% a participación y colaboración.

## Lo observado en UniFood

Aplicación Laravel con vistas Blade, Bootstrap y Vite. Incluye catálogo, carrito, pedidos de estudiantes, panel de personal, administración de productos, autenticación y vistas adaptables a móviles. Existen pruebas funcionales del flujo de pedidos y permisos, además de capturas previas en evidencias_sqa. La existencia de esos archivos no demuestra que las pruebas pasen hoy ni que se hayan cubierto todos los requisitos de la actividad.

## Primera etapa PWA implementada

- Manifiesto instalable con nombre, alcance, inicio y modo standalone.
- Iconos PNG de 192/512 px, icono maskable y Apple Touch; marca tipográfica UF.
- Botón de instalación cuando el navegador emite el evento correspondiente; ayuda para iOS.
- Service worker con caché limitado a siete recursos públicos y pantalla sin conexión.
- Aviso de pérdida de conexión en la interfaz existente.
- Ninguna página autenticada ni operación de pedido se almacena en Cache Storage. No hay envío automático al reconectar.
- Guía de instalación y casos manuales en docs/PWA.md y comando npm run test:pwa.

Para pedidos y datos actualizados se necesita internet. Esta etapa no incluye APK ni notificaciones push.

## Verificación real de esta sesión

| Comprobación | Resultado |
| --- | --- |
| npm ci | Completado; dependencias frontend instaladas |
| npm run build | Aprobado; Vite compiló los recursos de producción |
| npm run test:pwa | 6/6 aprobadas con entorno simulado |
| node --check del worker y módulo PWA | Aprobado |
| git diff --check | Sin errores de espacios; avisos habituales sobre CRLF |
| php artisan test | No ejecutó pruebas: falta vendor/autoload.php |
| Compatibilidad PHP | El proyecto declara ^8.3 y el PHP local localizado es 8.2.12 |
| Prueba de navegador automatizada | No completada: el navegador no logró iniciar en este entorno |
| Instalación Android/iOS y navegación móvil completa | Pendiente en dispositivo real y aplicación en ejecución |

La compilación mostró un aviso opcional de fontaine para optimizar fuentes; no impidió el build.

## Qué sigue para usarla en tu celular

1. Disponer de PHP compatible con el archivo composer.lock e instalar dependencias con composer install.
2. Preparar .env y una base de datos de desarrollo, generar la clave si falta y ejecutar migraciones/seed sin borrar datos existentes.
3. Ejecutar php artisan test y levantar la aplicación.
4. Probar mediante HTTPS accesible desde el teléfono. localhost del teléfono no es la PC; una IP HTTP permite ver la web pero no prueba completamente el service worker.
5. Instalar en Android o agregar a inicio desde Safari en iOS y ejecutar los casos PWA-01 a PWA-09 de docs/PWA.md.

## Próxima etapa SQA propuesta

| Entregable | Trabajo pendiente |
| --- | --- |
| Test Plan ágil | Obtener el plan anterior, historias, sprint y roles reales; completar los ocho apartados |
| Dos herramientas | Propuesta para acordar con el grupo: Cypress para automatización web y Squash para gestión/trazabilidad de casos; todavía no instaladas ni integradas |
| Tablero | Vincular historias, criterios de aceptación, casos y defectos en el tablero utilizado por el grupo |
| CI | Automatizar build, pruebas PWA y Laravel con versión PHP compatible; integrar resultados de las herramientas elegidas |
| Auditoría | Registrar observaciones, responsables, prioridad, corrección y nueva verificación |
| Métricas | Medir tasa de aprobación, defectos, cobertura cuando exista instrumentación y avance real; no inventar cifras |
| Evidencias | Capturas de instalación móvil, reportes de ejecución y trazabilidad a historias |

No se ha producido un Test Plan final ni afirmado cumplimiento completo del PDF. Primero se implementó la base móvil solicitada.

Referencias técnicas consultadas:
- https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Making_PWAs_installable
- https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API/Using_Service_Workers
