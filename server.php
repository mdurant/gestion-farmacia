<?php

/**
 * Router del servidor de desarrollo (`php artisan serve`).
 * Copia de Laravel con logging tolerante a "Broken pipe" (errno=32), habitual
 * con `composer dev` / concurrently o descargas CSV/PDF que cierran la conexión.
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];
$logLine = "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n";

// Evita Notice en pantalla si stdout ya no acepta escritura (cliente desconectado).
if (@file_put_contents('php://stdout', $logLine) === false) {
    // Sin log: la petición sigue procesándose con normalidad.
}

require_once $publicPath.'/index.php';
