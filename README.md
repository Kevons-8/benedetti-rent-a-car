# Benedetti Rent a Car

Sistema web para la gestión de alquiler de vehículos de Benedetti Rent a Car en Barranquilla, Colombia.

## Entornos

| Entorno | Propósito |
| --- | --- |
| Local (XAMPP) | Desarrollo y pruebas. |
| GitHub | Historial, revisión y fuente de versiones aprobadas. |
| Producción (InfinityFree) | Sitio disponible para clientes. |

La única ruta válida para publicar es: **local → GitHub → producción**.

## Ejecutar localmente

1. Copiar el proyecto al directorio web de XAMPP.
2. Crear una base de datos local compatible.
3. Copiar los archivos `config/*.example.php` a sus nombres sin `.example`.
4. Completar las credenciales locales en esos archivos, que no se suben a Git.
5. Iniciar Apache y MySQL desde el panel de XAMPP.

## Seguridad

No se deben versionar credenciales de base de datos, SMTP, FTP, API ni archivos `.env`.
Los archivos de configuración reales se mantienen exclusivamente en cada entorno; el repositorio incluye solo plantillas `*.example.php`.

## Flujo de cambios

Consulta [docs/WORKFLOW.md](docs/WORKFLOW.md) antes de hacer cambios o publicar el sitio.
