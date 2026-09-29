# Publicación en InfinityFree

## Antes de publicar

1. Probar el cambio completo en XAMPP.
2. Confirmar que la rama y el commit aprobados están en GitHub.
3. Ejecutar `composer install --no-dev` en una copia limpia del proyecto para preparar las dependencias PHP, incluido PHPMailer.
4. Si la publicación es manual por FTP, incluir la carpeta `vendor/` generada por Composer.
5. Respaldar la base de datos si el cambio la afecta.
6. No incluir archivos de configuración locales ni credenciales en la carga.

## Publicación manual inicial

1. Conectar por FTP a InfinityFree.
2. Cargar solo los archivos aprobados desde la versión de GitHub.
3. Conservar en el servidor los archivos `config/conexion.php`, `config/database.php` y `config/mail_config.php` con los datos reales de producción.
4. Abrir el sitio público y comprobar inicio, vehículos, reservas, pagos, inicio de sesión y correo cuando aplique.

## Automatización futura

Cuando el flujo manual esté estable, se podrá agregar un GitHub Action que despliegue únicamente cambios aprobados en `main` mediante FTP. Las credenciales FTP se guardarán como secretos de GitHub y nunca dentro del repositorio.

## Regla de recuperación

No editar directamente en producción. Ante una urgencia, replicar primero el cambio en local y registrarlo en GitHub; solo después publicarlo.
