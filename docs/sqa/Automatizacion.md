# Ejecución y evidencia de pruebas

Requisitos: PHP 8.4.1 o posterior compatible con composer.lock, Composer, Node 22 o compatible y Chrome. La integración continua utiliza PHP 8.4 y Node 22. Instalar las dependencias declaradas con `composer install` y `npm ci`.

## Backend y PWA

Ejecutar `php artisan test --compact` para la suite PHP. Las pruebas habituales utilizan SQLite en memoria; los casos de concurrencia crean una SQLite temporal compartida por procesos PHP independientes. Ejecutar `npm run test:pwa` para las pruebas del service worker.

La ejecución local del 28 de octubre de 2026 aprobó 58 pruebas PHP con 286 aserciones y seis pruebas de la PWA. Los reportes están en `evidencias_sqa/automatizacion/2026-10-28/phpunit.xml` y `pwa.xml`. `regresion-antes.xml` conserva siete regresiones fallidas antes de corregir sus comportamientos; es evidencia del ciclo de corrección, no el resultado final del backend.

## Navegador en Windows

Desde la raíz del proyecto ejecutar:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File docs/sqa/Ejecutar-Pruebas-SQA.ps1
```

El script utiliza una base SQLite exclusiva, prepara los usuarios de prueba, compila la interfaz y ejecuta Cypress y Selenium. El parámetro `Salida` permite conservar otra campaña en una carpeta distinta; los reportes deben indicar la ejecución y el código evaluado. La base de la demostración se conserva separada de los datos E2E.

## Ejecución manual de las suites de navegador

Usar una base SQLite exclusiva para E2E. Antes de migrar, establecer `DB_CONNECTION=sqlite` y `DB_DATABASE` con la ruta absoluta del archivo vacío de prueba. Establecer `SESSION_COOKIE=unifood_e2e`, `SESSION_DRIVER=file`, `CACHE_STORE=database` y `APP_URL=http://127.0.0.1:8011`. La caché compartida permite comprobar controles entre solicitudes HTTP. No usar la base de la demostración.

Ejecutar `php artisan migrate --seed --force --no-interaction` y `php artisan serve --host=127.0.0.1 --port=8011`. Conservar esa terminal abierta. En otra terminal ejecutar `npm run test:cypress -- --browser chrome` y `npm run test:selenium`. `E2E_URL` permite cambiar la dirección. Selenium admite `CHROMEDRIVER` y `CHROME_BINARY` si se emplea un controlador instalado manualmente.

Los usuarios sembrados de esta base de prueba tienen contraseña `password`. Son cuentas de demostración. Cypress conserva XML JUnit y capturas; Selenium conserva JSON y capturas. `SQA_OUTPUT_DIR` permite elegir una carpeta de resultados; sin esa variable se utiliza `evidencias_sqa/automatizacion`. Para acreditar una ejecución, consultar reportes y códigos de salida.

## Integración continua y campaña histórica

El workflow Calidad UniFood se ejecuta al publicar un commit, abrir un pull request o activarlo manualmente. Un archivo YAML local no acredita una ejecución aprobada en GitHub Actions.


Campaña histórica aprobada: https://github.com/ifrancro/UniFood/actions/runs/40628543184, commit `14774aa01fc7d735df3a5dc9e6b65cdff87d1f32`. PHPUnit 14/14, PWA 6/6, Cypress 7/7 y Selenium 4/4. Los XML, JSON y capturas originales están en el artefacto `evidencias-calidad`. Esta campaña se mantiene separada de la actualización local del 28 de octubre y de las ejecuciones que evalúen los cambios posteriores.

La [actualización técnica](Actualizacion_SQA_2026-10-28.md) relaciona los cambios de esta revisión con la evidencia de cierre.
