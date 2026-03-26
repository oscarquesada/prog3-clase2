<?php
declare(strict_types=1); // Modo estricto de tipos en PHP

// Leer el método HTTP del request (GET, POST, PUT, DELETE, etc.)
// Si no existe por algún motivo, asumir GET
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Leer la URL completa que pidió el navegador
// Ej: /prog3-clase2/public/health?foo=bar
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Sacar solo el path, sin query string (?foo=bar)
// Resultado: /prog3-clase2/public/health
$path = parse_url($requestUri, PHP_URL_PATH);

// Estas dos líneas las dejó el profe pero no se usan en esta versión
// $scriptName sería algo como /prog3-clase2/public/index.php
// $scriptDir sería /prog3-clase2/public
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir  = str_replace('\\', '/', dirname($scriptName));

// Limpiar el path sacando el prefijo del proyecto
// Si llega /prog3-clase2/public/health → queda /health
// Si llega /prog3-clase2/health → queda /health
// El (/public)? significa que /public es opcional
$path = preg_replace('#^/prog3-clase2(/public)?#', '', $path);

// Asegurar que el path siempre empiece con /
// ltrim saca las barras del inicio, después le agregamos una sola
$path = '/' . ltrim((string)$path, '/');

// Caso borde: si quedó // convertirlo a /
if ($path === '//') {
    $path = '/';
}

// ---- RUTAS ----

// Ruta GET /health
// Devuelve información del servidor en JSON - sirve para verificar que la API está viva
if ($method === 'GET' && $path === '/health') {
    header('Content-Type: application/json; charset=utf-8'); // Decirle al navegador que es JSON
    http_response_code(200); // 200 = OK
    echo json_encode([
        'status'      => 'ok',
        'timestamp'   => date('Y-m-d H:i:s'), // Fecha y hora actual del servidor
        'php_version' => phpversion(),          // Versión de PHP instalada
        'server'      => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache' // Info del servidor web
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); // Pretty print = JSON indentado y legible
    exit; // Parar ejecución, no seguir evaluando rutas
}

// Ruta GET / (raíz)
// Devuelve un mensaje básico indicando que la API funciona
if ($method === 'GET' && $path === '/') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    echo json_encode([
        'message' => 'API funcionando',
        'health'  => '/health' // Le avisa al usuario que existe el endpoint /health
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Si ninguna ruta coincidió, devolver 404 Not Found
// Incluye el path que se intentó para facilitar el debugging
header('Content-Type: application/json; charset=utf-8');
http_response_code(404); // 404 = No encontrado
echo json_encode([
    'error' => 'Not Found',
    'path'  => $path // Muestra qué ruta se intentó acceder
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);