# DevQuick Monitor SDK para PHP

## Descripción
El **DevQuick Monitor SDK** es una librería PHP diseñada para capturar y reportar errores de manera eficiente a un servidor de monitoreo. Facilita la integración con cualquier aplicación PHP, permitiendo registrar excepciones automáticamente.

## Características
- Captura y envía errores automáticamente.
- Integración sencilla con aplicaciones PHP.
- Uso de `dotenv` para configuración flexible.
- Envía los reportes al backend de monitoreo DevQuick.

## Requisitos
- PHP 8.0 o superior.
- Composer instalado.
- Extensiones `curl` y `json` habilitadas.

## Instalación
Para instalar el SDK en tu proyecto, ejecuta el siguiente comando:

```sh
composer require devquick/monitor-sdk
```

## Configuración
### 1. Crear un archivo `.env`
Debes configurar tu API Key y la URL del servidor de monitoreo en un archivo `.env` en la raíz del proyecto:

```ini
# Configuración de DevQuick Monitor SDK
DEVQUICK_MONITOR_API_KEY=tu_api_key
DEVQUICK_MONITOR_API_URL=https://tu-servidor.com/api/report
```

### 2. Cargar las variables de entorno en PHP
Si estás usando un entorno PHP sin frameworks, asegúrate de cargar el archivo `.env`:

```php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = getenv('DEVQUICK_MONITOR_API_KEY');
$apiUrl = getenv('DEVQUICK_MONITOR_API_URL');
```

## Licencia
Este proyecto está licenciado bajo la licencia MIT. Puedes usarlo libremente en tus proyectos.

## Contacto
Si necesitas soporte o tienes alguna consulta, puedes contactarnos en:
- **Email:** soporte@devquick.com
- **Sitio web:** [devquick.com](https://devquick.com)

