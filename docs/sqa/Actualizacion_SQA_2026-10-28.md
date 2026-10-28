# Actualización técnica del Test Plan de UniFood

Fecha: 28 de octubre de 2026, Bolivia. Esta adenda complementa el Test Plan TP-UF-01 y el informe versión 1.3. Identifica las verificaciones de las mejoras publicadas después de la línea base e5631aa. El informe reciente de cinco páginas explica el trabajo realizado; el Test Plan original conserva la estrategia y los criterios completos.

## Objetivo y alcance de la actualización

Verificar confirmación idempotente, consistencia de estados, reserva persistente de códigos, exactitud monetaria y validaciones del servidor. Se conservan autenticación, roles, privacidad, catálogo, carrito, atención y administración del sistema existente. La ejecución combina pruebas funcionales, unitarias, de integración, concurrencia y navegador.

## Cambios y trazabilidad

| Tarea | Cambio comprobado | Requisitos e historias | Regresión |
| --- | --- | --- | --- |
| [MEJ-01 #13](https://github.com/ifrancro/UniFood/issues/13) | UUID obligatorio, token único persistido y recuperación de sesiones antiguas sin borrar un carrito nuevo | RF-04, HU-04 | PedidoSafetyTest, EvitarPedidosDuplicadosTest, ConcurrenciaPedidosTest y CP-18 de Cypress |
| [MEJ-02 #14](https://github.com/ifrancro/UniFood/issues/14) | Avance condicionado al estado mostrado y cancelación limitada al estado pendiente | RF-06/09, HU-06/09 | ProteccionCambiosEstadoTest, PedidoSafetyTest, ConcurrenciaPedidosTest y CP-18 |
| [MEJ-03 #15](https://github.com/ifrancro/UniFood/issues/15) | Contador diario persistente y migración que conserva pedidos históricos | RF-04, HU-04 | GeneracionSeguraCodigoPedidoTest y ConcurrenciaPedidosTest |
| [MEJ-04 #16](https://github.com/ifrancro/UniFood/issues/16) | Centavos enteros, cantidades acumuladas hasta 50, precisión/rango del precio y capacidad del total; descripción opcional | RF-03/04/07, HU-03/04/07 | ImporteTest, PedidoSafetyTest y AdminProductosTest |
| [MEJ-05 #17](https://github.com/ifrancro/UniFood/issues/17) | Bloqueo tras cinco fallos, limpieza del contador y acceso tras vencer la espera | RF-01, HU-01 | RateLimitingLoginTest |
| [SQA-01 #18](https://github.com/ifrancro/UniFood/issues/18) | Runner portable, servidor desde public, comprobación del formulario y evidencias separadas | Test Plan, CP-01/02/06/13/16/18 | Cypress y Selenium |

La revisión técnica detectó que un reenvío podía dejar un token consumido en una sesión antigua. La corrección retira ese token y su carrito únicamente cuando corresponden a la confirmación repetida; conserva cualquier carrito con token nuevo. Ambas ramas tienen regresión.

## Casos adicionales del plan

| Caso | Condición | Resultado esperado y comprobado |
| --- | --- | --- |
| CP-18 | Confirmar una compra y reenviar el mismo token; repetir un avance desde la pantalla antigua | Un pedido y un solo avance de estado; verificación desde Chrome con Cypress |
| CP-19 | Dos procesos PHP confirman el mismo token sobre una SQLite compartida | Un pedido completo y un detalle, sin duplicación |
| CP-20 | Dos procesos reservan códigos del mismo día | Dos códigos distintos |
| CP-21 | Dos procesos avanzan desde el mismo estado observado | Un solo avance |
| CP-22 | Cancelación e inicio de preparación compiten con modelos cargados antes de la barrera | Una sola operación aplicada, sin sobrescritura del estado |
| CP-23 | Precio 0.10 por tres unidades; entradas con precisión o cantidades inválidas | Total exacto 0.30 y rechazo de entradas fuera de las reglas |
| CP-24 | Migración inversa y nueva aplicación sobre una base temporal con pedido histórico | Pedido y total conservados, token histórico nulo y numeración continuada |
| CP-25 | Bloqueo de login y avance del reloj 61 segundos | Acceso permitido después del vencimiento |

## Entorno y resultados locales

Laravel 13.31.0, PHPUnit 12.5.34, PHP 8.4, Node 24, Cypress 16.2.0 y Selenium WebDriver 4.51.0. La campaña de navegador utilizó Chrome 156 headless en Windows. CI configura PHP 8.4 y Node 22 conforme al workflow del repositorio.

| Suite | Ejecutadas | Aprobadas | Evidencia |
| --- | --- | --- | --- |
| PHPUnit | 58 | 58 | [phpunit.xml](../../evidencias_sqa/automatizacion/2026-10-28/phpunit.xml), 286 aserciones |
| Service worker PWA | 6 | 6 | [pwa.xml](../../evidencias_sqa/automatizacion/2026-10-28/pwa.xml) |
| Cypress | 8 | 8 | [Reporte XML](../../evidencias_sqa/automatizacion/2026-10-28/cypress.xml) y tres capturas |
| Selenium | 4 | 4 | [Resultados JSON](../../evidencias_sqa/automatizacion/2026-10-28/selenium-resultados.json) y dos capturas |

La tasa de aprobación es 100% en cada una de estas suites y en esta ejecución. Se distingue de la cobertura de sentencias, ramas y requisitos completos. Los cuatro escenarios de dos procesos comprueban los conflictos seleccionados; no representan un ensayo de carga masiva.

[Resumen de navegador](../../evidencias_sqa/automatizacion/2026-10-28/resumen-navegador.json). La compilación con Vite y el formateo con Pint se ejecutaron correctamente. [Regresiones iniciales](../../evidencias_sqa/automatizacion/2026-10-28/regresion-antes.xml): siete pruebas fallidas antes de corregir el comportamiento, conservadas como evidencia del ciclo de corrección.

El primer intento local de navegador detectó un error de infraestructura: el router utilizaba el directorio del proyecto como directorio público. El runner ahora arranca desde public y comprueba que la respuesta contiene el formulario de login. El intento anterior se conserva en una carpeta diferenciada; la campaña final de navegador terminó con código 0.

La revisión técnica reforzó también el aislamiento del runner: neutraliza DB_URL y rechaza una configuración Laravel cacheada antes de migrar. No modifica ni limpia automáticamente la configuración habitual del usuario.

## Aislamiento de datos y migración

Las pruebas funcionales utilizan SQLite en memoria. Los conflictos entre procesos y la migración inversa se prueban sobre archivos SQLite temporales. El navegador utiliza una base E2E exclusiva, sesiones separadas y caché database en esa misma base. El servidor temporal se detiene al finalizar.

La migración 2026_10_28_193703 agrega order_token nullable con índice único y la tabla pedido_contadores. Se aplicó también en la base de demostración después de crear una copia de seguridad privada local. Los pedidos existentes se conservan. La reversión elimina el token y sus reservas técnicas; no elimina pedidos, pero retira las protecciones nuevas y no se usa para la demostración.

## Integración con el tablero

[Tablero público administrado por ifrancro](https://github.com/users/ifrancro/projects/3), vinculado a [UniFood](https://github.com/ifrancro/UniFood). Columnas: Backlog, En curso, En revisión y Terminado. HU-01 a HU-10 mantienen sus criterios de aceptación y enlazan pruebas y evidencia. MEJ-01 a MEJ-05 y SQA-01 registran el cierre técnico de esta actualización.

Prioridad alta: acceso, catálogo, carrito, creación y privacidad del pedido, estados y administración. Prioridad media: cancelación y experiencia móvil. Las historias se someten a revisión del equipo; cerrar una mejora técnica no equivale a aceptar todos los criterios de una historia amplia. Las responsabilidades y eventos efectivos los gestiona el equipo.

## Integración continua y consulta de evidencias

El workflow [Calidad UniFood](https://github.com/ifrancro/UniFood/actions/workflows/calidad.yml) instala las dependencias, compila la interfaz, ejecuta las cuatro suites y publica el artefacto evidencias-calidad. La ejecución vinculada al commit publicado puede consultarse en Actions y en las tareas del tablero.

La campaña histórica [40628543184](https://github.com/ifrancro/UniFood/actions/runs/40628543184) conserva sus resultados 14 PHP, 6 PWA, 7 Cypress y 4 Selenium sobre 14774aa01fc7. No se mezcla con la campaña de esta adenda.

## Material de exposición

- [Informe reciente sin carátula en PDF](entrega/UniFood_Informe_Trabajo_Realizado.pdf).
- [Informe reciente editable](entrega/UniFood_Informe_Trabajo_Realizado.docx).
- [Guion para Ian, Kathleen y Sergio](Guion_Exposicion_3_Personas.md).
- [Test Plan original y organización Scrum](entrega/UniFood_Informe_SQA_Scrum.pdf).
- [Repetir la campaña](Automatizacion.md).
