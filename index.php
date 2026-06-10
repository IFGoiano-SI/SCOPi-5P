<?php

ob_start();
session_start();

require_once __DIR__ . '/Config/Helpers.php';
require_once __DIR__ . '/Autoloader.php';

define('APP_ROOT', __DIR__);

// Parser de arquivo .env simples
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove ponto e vírgula no final se houver (evita erros se copiado como código PHP)
        if (str_ends_with($value, ';')) {
            $value = rtrim(substr($value, 0, -1));
        }
        
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

define('HOST', getenv('HOST'));
define('NAME', getenv('NAME'));
define('USER', getenv('USER'));
define('PASS', getenv('PASS'));
define('PORT', getenv('PORT'));

if (!defined('BASE_URL')) {
    define('BASE_URL', base_url());
}

$routes = require __DIR__ . '/Config/Routes.php';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

$uri = '/' . trim($uri, '/');
$uri = $uri === '//' ? '/' : $uri;

function matchRoute(string $uri, array $routes): array
{
    foreach ($routes as $route => $handler) {
        $pattern = preg_replace('/\{[^\/]+\}/', '([^\/]+)', $route);
        $pattern = '#^' . rtrim($pattern, '/') . '$#';

        if (preg_match($pattern, rtrim($uri, '/') ?: '/', $matches)) {
            array_shift($matches);
            return [$handler, $matches];
        }
    }

    return [null, []];
}

[$handler, $params] = matchRoute($uri, $routes);

if ($handler === null) {
    header('Location: ' . base_url(empty($_SESSION['usuario_id']) ? 'login' : 'inicio'));
    exit;
}

[$controllerName, $method] = $handler;
$className = "\\Controllers\\{$controllerName}";

if (!class_exists($className)) {
    http_response_code(404);
    echo "Controller '{$controllerName}' nao encontrado.";
    ob_end_flush();
    exit;
}

$controller = new $className();

if (!method_exists($controller, $method)) {
    http_response_code(404);
    echo "Metodo '{$method}' nao encontrado em {$controllerName}.";
    ob_end_flush();
    exit;
}

call_user_func_array([$controller, $method], $params);
ob_end_flush();
