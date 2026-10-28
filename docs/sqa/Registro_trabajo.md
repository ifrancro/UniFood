# Registro técnico de la entrega

Fecha de corte: 27 de octubre de 2026, Bolivia. Preparación técnica asistida con Codex. Roles confirmados por el usuario; la participación individual y las aprobaciones de cada integrante no se presuponen.

| Actividad | Evidencia | Resultado |
| --- | --- | --- |
| Integrar unidades 1, 2 y 3 | Informe SQA-UF-01 versión 1.3 | Requisitos, modelos, Test Plan, riesgos, casos y trazabilidad |
| Corregir casillas | AdminProductosTest | DEF-01 y DEF-02 corregidos |
| Corregir recuperación | PasswordRecoveryTest e issue 12 | DEF-03 corregido: hash, vencimiento, uso único y limitación de solicitudes |
| Ejecutar backend y PWA | XML JUnit en CI | PHP 14 pruebas, 110 aserciones; PWA seis pruebas; aprobadas |
| Ejecutar Cypress | XML y capturas | Siete pruebas aprobadas |
| Ejecutar Selenium | JSON y capturas | Cuatro pruebas aprobadas, sesiones independientes |
| Integrar CI | https://github.com/ifrancro/UniFood/actions/runs/40628543184 | Todos los pasos aprobados sobre 14774aa01fc7d735df3a5dc9e6b65cdff87d1f32 |
| Publicar backlog | Issues 2 a 11 y GitHub Project | Diez historias con criterios y casos |
| Confirmar organización | Acuerdo_roles.md | Ian PO, Kathleen SM, Sergio Developer; calidad compartida |

## Reuniones y revisión del equipo

No hay evidencia de eventos ya celebrados. Usar Formatos_Scrum.md para registrar fecha, participantes, decisiones y evidencia reales. Planning define alcance y capacidad; Daily revisa avance e impedimentos; Review demuestra y acepta; Retrospective acuerda una mejora concreta. No completar firmas o asistencia sin participación efectiva.

## Actualización técnica del 28 de octubre de 2026

| Actividad | Evidencia | Resultado |
| --- | --- | --- |
| Reforzar confirmación y recuperación de sesiones antiguas | MEJ-01, PedidoSafetyTest y CP-18 | Un solo pedido por token; carrito nuevo preservado |
| Proteger transiciones y cancelación | MEJ-02 y pruebas de dos procesos | Avance único y una sola operación en la carrera cancelar/preparar |
| Persistir correlativo y aplicar migración | MEJ-03, regresión de migración y migrate:status | Pedidos históricos conservados; migración aplicada en demostración con copia privada previa |
| Calcular centavos y validar entradas | MEJ-04, ImporteTest y PedidoSafetyTest | Aritmética exacta, límites y descripción opcional |
| Verificar expiración del bloqueo de login | MEJ-05 y RateLimitingLoginTest | Acceso permitido al vencer la espera |
| Ejecutar campaña local | Reportes de automatizacion/2026-10-28 | PHP 58/58, 286 aserciones; PWA 6/6; Cypress 8/8; Selenium 4/4 |
| Corregir arranque del runner | SQA-01, resumen-navegador.json y capturas | Directorio público correcto, login comprobado y ejecución con salida 0 |
| Preparar tablero administrable | https://github.com/users/ifrancro/projects/3 | Diez historias En revisión, seis mejoras técnicas y prioridades enlazadas |

El fallo inicial del router se conserva por separado del resultado aprobado. La revisión técnica asistida incluyó una segunda revisión de código; esta no atribuye asistencia ni aprobaciones humanas a integrantes del equipo.
