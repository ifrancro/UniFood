# Guion de exposición de UniFood: calidad, pruebas y mejora continua

**Duración propuesta:** 12 a 15 minutos, incluida una demostración de 2 a 3 minutos.
**Integrantes:** Ian Romero, Kathleen Barrientos y Sergio Iporre.
**Fecha de preparación:** 28 de octubre de 2026, Bolivia.
**Referencia de resultados:** [Actualización técnica del 28 de octubre](Actualizacion_SQA_2026-10-28.md), [informe del trabajo realizado](Informe_Trabajo_Realizado_UniFood.md) y reportes de cada campaña.

Este guion explica lo implementado y comprobado. Los roles de exposición pueden intercambiarse; son distintos de los roles de acceso del sistema. Cada intervención se apoya en documentos, código o resultados identificables.

## Distribución y material en pantalla

| Turno | Persona | Tema | Mostrar |
| --- | --- | --- | --- |
| 1 | Ian | Problema, alcance y requisitos | Catálogo, actores y requisitos del informe |
| 2 | Kathleen | Test Plan, Scrum y control de calidad | Casos, trazabilidad, tablero y evidencias |
| 3 | Sergio | Correcciones y verificación técnica | Regresiones, resultados y recorrido del pedido |
| Demo y cierre | Los tres | Estudiante, personal y evaluación | Un pedido completo y su evidencia |

## Ian Romero: qué problema resolvemos y qué significa calidad

**Tiempo orientativo: 3 a 4 minutos.**

“Buenos días. Presentamos UniFood, un sistema web para consultar el menú universitario, preparar un carrito y solicitar comida para recogerla en la tienda. El pago se realiza al recoger; el proyecto no procesa pagos en línea.

El sistema tiene tres actores. El estudiante consulta el catálogo, confirma y revisa sus pedidos. El empleado organiza la preparación y la entrega. El administrador mantiene los productos, sus precios y su disponibilidad. Estos permisos pertenecen a la aplicación y son diferentes de nuestras responsabilidades dentro de Scrum.

Para este trabajo no bastaba con que las pantallas se vieran terminadas. La actividad pide demostrar cómo incorporamos la calidad durante el desarrollo: definir qué esperamos, diseñar pruebas, identificar defectos, corregirlos y conservar evidencia de los resultados.

Por eso describimos requisitos verificables. Por ejemplo: un estudiante solo puede consultar sus pedidos; un pedido necesita productos válidos; los importes se calculan con precios de la base de datos; un pedido entregado no debe modificarse; y el estudiante solo puede cancelar mientras el pedido siga pendiente.

La calidad se observa con situaciones concretas. Si dos estudiantes compran al mismo tiempo, sus pedidos deben ser identificables. Si alguien repite una confirmación, no queremos registrar otra compra por accidente. Si un estudiante intenta consultar un pedido ajeno, el servidor debe rechazarlo aunque escriba la dirección manualmente.

Nuestro alcance comprende autenticación, catálogo, carrito, pedidos y administración. También hay una PWA con pantalla sin conexión. Eso no significa que se puedan enviar pedidos sin internet: las compras necesitan el servidor.

En las responsabilidades acordadas, yo ejerzo como Product Owner; Kathleen como Scrum Master; y Sergio tiene foco en desarrollo y automatización. La calidad y las pruebas son compartidas. La revisión de los criterios de aceptación permite relacionar las necesidades de la tienda con el comportamiento del sistema.

Ahora Kathleen explicará cómo convertimos esas reglas en un plan de pruebas y en evidencia revisable.”

**Apoyo visual:** mostrar el catálogo y señalar un requisito funcional, una regla de negocio y una historia de usuario. No dedicar este turno a leer código.

## Kathleen Barrientos: cómo organizamos y comprobamos la calidad

**Tiempo orientativo: 3 a 4 minutos.**

“El Test Plan define qué se prueba, cómo, con qué herramientas y con qué condiciones de entrada y salida. Incluye alcance, estrategia, responsabilidades, ciclo de pruebas, métricas y riesgos. Su actualización permite acompañar los cambios del producto.

Una distinción importante es QA y QC. QA se ocupa del proceso para prevenir problemas: revisar requisitos, acordar criterios y planificar verificaciones. QC comprueba el producto: ejecutar un caso, observar el resultado y compararlo con lo esperado.

La trazabilidad conecta requisito, historia, caso y evidencia. Por ejemplo, el requisito de privacidad se relaciona con la historia de consultar pedidos propios. Su caso intenta acceder al pedido de otro estudiante; el resultado esperado es el rechazo y la protección de los datos. El reporte identifica la versión y el resultado real.

Priorizamos por riesgo e impacto. La confirmación de una compra, el estado del pedido, los totales y los permisos tienen más importancia inmediata que añadir funciones de comodidad. Por eso las últimas correcciones se concentraron en esos puntos.

La estrategia combina varias capas. PHPUnit comprueba lógica y comportamiento del servidor. Las pruebas JavaScript revisan el service worker de la PWA. Cypress y Selenium verifican recorridos desde navegador; son las dos herramientas de la lista solicitada por la actividad. GitHub Actions permite ejecutar automáticamente la campaña cuando se integran cambios.

Pasar una prueba y medir cobertura son cosas distintas. Una tasa de aprobación del cien por ciento significa que todas las pruebas ejecutadas pasaron. No demuestra que hayamos probado todas las situaciones del sistema. También distinguimos las pruebas de esta actualización local de la campaña histórica que ya pasó en GitHub.

En Scrum, el tablero relaciona historias con casos y evidencias. Para evaluar una historia revisamos sus criterios de aceptación y el resultado de sus pruebas. La documentación distingue la verificación técnica de la aceptación del producto y asigna responsabilidades claras para cada actividad.

La mejora continua se ve en el ciclo de encontrar un problema, reproducirlo, corregirlo y volver a probar. Conservamos un reporte de regresiones fallidas antes de los cambios y los resultados posteriores. Sergio mostrará los ejemplos técnicos y explicará qué garantía aporta cada uno.”

**Apoyo visual:** abrir el Test Plan, una relación RF/HU/CP y los reportes. Si se muestra el tablero, describir su estado real y no cambiar tarjetas únicamente para la exposición.

## Sergio Iporre: qué se corrigió y cómo sabemos que funciona

**Tiempo orientativo: 4 minutos.**

“La revisión detectó que varias protecciones ya estaban iniciadas, pero necesitaban reforzarse. Primero escribimos pruebas para reproducir los problemas. Siete regresiones iniciales fallaron sobre el comportamiento anterior; conservamos ese reporte. Otra prueba mostró que limpiar la caché podía repetir un código reservado.

La primera corrección es la confirmación de pedidos. Ahora el servidor exige un identificador de confirmación y lo guarda con una restricción única en la base de datos. Repetir esa misma confirmación no crea otro pedido, incluso si llega una copia antigua de la sesión. Además, el botón se desactiva mientras se envía. Esto se llama idempotencia: repetir la misma operación conserva su efecto único.

La segunda corrección protege los estados. El formulario del empleado envía el estado que mostraba la pantalla. El servidor actualiza solo si ese estado sigue vigente. Si otra acción ya lo cambió, pide actualizar la vista. La cancelación también se aplica únicamente cuando el pedido continúa pendiente.

Esto importa porque la aplicación usa SQLite. No nos apoyamos en un supuesto bloqueo de filas que ese motor no aplica con lockForUpdate. Usamos actualizaciones condicionales y una configuración transaccional apropiada para las escrituras. Si cancelar e iniciar preparación compiten, solo una de esas operaciones puede aplicarse.

La tercera corrección mueve la numeración diaria a un contador persistente en la base de datos. Ya no depende de conservar un contador en caché. El formato del código sigue siendo UF, fecha y correlativo. Puede haber huecos; lo importante es no prometer una numeración sin huecos y mantener códigos únicos.

La cuarta corrección calcula importes en centavos enteros. Diez centavos por tres unidades se calcula como diez por tres: treinta centavos. Al guardar y mostrar se convierte a 0.30. También se validan cantidades y precios en el servidor; las restricciones de los controles del navegador no son suficientes.

Comprobamos las correcciones con pruebas funcionales, pruebas unitarias de importes y cuatro escenarios de concurrencia con dos procesos PHP separados que comparten una base SQLite temporal. También probamos que la migración conserva los pedidos anteriores. Estas pruebas son más concretas que enviar dos solicitudes una después de otra dentro del mismo proceso.

La revisión local del 28 de octubre aprobó 58 pruebas PHP con 286 aserciones y seis pruebas del service worker. La campaña histórica de GitHub Actions aprobó siete pruebas de Cypress y cuatro de Selenium sobre la versión que identifica su reporte. Cada campaña conserva su resultado y su versión; no sumamos ejecuciones distintas como si fueran una sola. Los resultados exactos del cierre se consultan en la actualización técnica y sus reportes. La tasa de aprobación expresa los casos ejecutados y se distingue de la cobertura total del sistema.”

**Apoyo visual:** mostrar el XML anterior, el resultado actual y los nombres de los escenarios concurrentes. Leer las cifras de la adenda final; no reutilizar las cifras de la versión 1.3 como si fueran actuales.

## Demostración coordinada: 2 a 3 minutos

Preparar datos de demostración y sesiones separadas; no emplear la base temporal de los tests, que se elimina o queda aislada.

1. **Ian, como estudiante:** iniciar sesión, elegir un producto y revisar cantidad y total del carrito. Confirmar y mostrar el código, detalle y estado pendiente.
2. **Sergio, como empleado:** abrir el pedido y avanzar a preparación, listo y entregado. Explicar que cada paso responde a una acción distinta. Mostrar que un entregado ya no ofrece avance.
3. **Kathleen:** relacionar el recorrido con sus casos y mostrar la evidencia automatizada de repetición de confirmación y conflicto de estados. Los escenarios concurrentes se demuestran mediante su prueba reproducible, no haciendo clics rápidos y suponiendo que fueron simultáneos.
4. Si el tiempo lo permite, mostrar una casilla Disponible o Activo con un producto exclusivo de prueba. No desactivar productos de la demostración innecesariamente.

**Cierre sugerido, Kathleen:** “La evidencia relaciona lo que pedimos al sistema con lo que comprobamos. Priorizamos riesgos, corregimos comportamientos concretos y conservamos regresiones para repetir la evaluación después de nuevos cambios. Así podemos explicar cada mejora desde su requisito hasta su resultado.”

## Preguntas probables y respuestas

**¿Qué diferencia hay entre una historia y un caso de prueba?**
La historia expresa una necesidad del usuario y su valor. El caso establece datos, pasos y resultado esperado para comprobar una parte de esa necesidad.

**¿Por qué desactivar el botón no es suficiente?**
Una solicitud puede repetirse por reintentos de red o enviarse sin usar el botón. Por eso la protección decisiva está en el servidor y en la base de datos.

**¿Qué es concurrencia?**
Son operaciones que se ejecutan solapadas. Dos procesos que intentan confirmar o cambiar un pedido pueden competir por el mismo dato.

**¿Una transacción resuelve cualquier carrera?**
No. También importa qué dato se comprueba, en qué momento y qué garantiza el motor. La actualización condicional une la comprobación del estado y su modificación en una operación.

**¿Por qué centavos si la columna ya era decimal?**
El formato de almacenamiento no impide que PHP calcule antes con float. La corrección cambia la aritmética del carrito y del pedido; la BD sigue almacenando decimales.

**¿Todo el proyecto se hizo con TDD?**
No tenemos evidencia para afirmar eso. En esta actualización sí se escribieron regresiones, se observaron fallos y después se corrigió el código. El reporte anterior a la corrección y las ejecuciones posteriores respaldan ese ciclo concreto.

**¿Tienen BDD automatizado?**
Hay escenarios de comportamiento documentados. Eso no significa que exista un ejecutor Gherkin/BDD configurado. Las pruebas ejecutables actuales usan PHPUnit, Node, Cypress y Selenium.

**¿Tienen CI/CD?**
Hay integración continua: compilación y pruebas automáticas en GitHub Actions. El workflow conserva resultados de las cuatro suites como artefactos. Cada campaña se identifica mediante su enlace y el código que evaluó. Esta automatización de compilación y pruebas es diferente de un despliegue automático a producción.

**¿Aprobar todas las pruebas significa que no hay defectos?**
No. Significa que los casos ejecutados pasaron bajo el entorno declarado. La cobertura global, otros navegadores y ensayos de carga requieren evidencias adicionales.

**¿Qué mejoras aportó esta revisión?**
La confirmación tiene un identificador persistente y único; los cambios de estado son condicionales; la numeración diaria se guarda en la base de datos; los importes se calculan en centavos; y las validaciones del servidor tienen regresiones específicas.

**¿Qué demuestran las pruebas de dos procesos?**
Que los cuatro conflictos seleccionados se ejecutaron en procesos PHP separados sobre una misma SQLite temporal. No representan todos los niveles posibles de carga ni certifican otros motores de BD.

## Preparación y reparto de preguntas

- **Ian:** estudiar alcance, actores, requisitos, criterios de aceptación y prioridad de las mejoras.
- **Kathleen:** estudiar QA/QC, Test Plan, trazabilidad, Scrum y lectura de métricas.
- **Sergio:** estudiar token único, actualización condicional, contador en BD, centavos, migración y pruebas.
- Los tres deben poder explicar un defecto de principio a fin: situación, riesgo, prueba que falló, corrección y evidencia posterior.
- Ensayar con un cronómetro y evitar leer archivos enteros de código. Tener abiertos catálogo, panel, Test Plan y resumen de resultados.
- Antes del examen, revisar los cambios, ejecutar las pruebas de la versión que se presentará y confirmar que sus reportes coinciden con lo que se dirá.
