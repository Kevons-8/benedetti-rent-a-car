# Flujo de trabajo

## Regla principal

La ruta de publicación es siempre: **local → GitHub → producción**. No se edita código directamente en InfinityFree.

## Cambios normales

1. Actualizar la rama `main` en el equipo local.
2. Crear una rama descriptiva, por ejemplo `feature/home-redesign`.
3. Probar el cambio en XAMPP.
4. Registrar el cambio en Git y subirlo a GitHub.
5. Revisar el cambio antes de integrarlo a `main`.
6. Publicar `main` en InfinityFree por FTP. Más adelante, este paso podrá automatizarse con GitHub Actions.

## Configuración y secretos

Los archivos `config/conexion.php`, `config/database.php` y `config/mail_config.php` contienen datos particulares de cada entorno y no se versionan.

- Para XAMPP, crear las copias locales desde los archivos `*.example.php` y completar los datos de desarrollo.
- Para InfinityFree, conservar los archivos reales exclusivamente en el servidor.
- No subir contraseñas, datos de FTP, copias de la base de datos ni claves de correo a GitHub.

## Cambios urgentes en producción

Evitar editar producción. Si fuera inevitable, descargar de inmediato el archivo modificado, replicar el cambio localmente y registrarlo en GitHub antes de realizar cualquier trabajo adicional.
