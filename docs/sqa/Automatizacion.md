# Ejecución de pruebas de navegador

Requisitos: PHP 8.4, Composer, Node 22 y Chrome. Ejecutar npm ci, composer install y npm run build.

Usar una base SQLite exclusiva para E2E. Antes de migrar, establecer DB_CONNECTION=sqlite y DB_DATABASE con la ruta absoluta del archivo vacío de prueba. Establecer SESSION_COOKIE=unifood_e2e, SESSION_DRIVER=file y APP_URL=http://127.0.0.1:8011. No usar la base de la demostración.

Ejecutar php artisan migrate --seed --force y php artisan serve --host=127.0.0.1 --port=8011. Conservar esa terminal abierta. En otra terminal ejecutar npm run test:cypress y npm run test:selenium. E2E_URL permite cambiar la dirección. Selenium admite CHROMEDRIVER y CHROME_BINARY si se emplea un controlador instalado manualmente.

Los usuarios sembrados de esta base de prueba tienen contraseña password. No son credenciales de producción. Cypress conserva XML JUnit y capturas; Selenium conserva JSON y capturas en evidencias_sqa/automatizacion. No confundir los casos preparados con pruebas ejecutadas. Consultar sus reportes y códigos de salida.

El workflow Calidad UniFood se ejecuta al publicar un commit, abrir un pull request o activarlo manualmente. Un archivo YAML local no acredita una ejecución aprobada en GitHub Actions.
