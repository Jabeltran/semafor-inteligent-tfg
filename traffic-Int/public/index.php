<?php
require_once __DIR__ . '/../app/init.php';

$url = $_GET['url'] ?? 'auth/login'; // Ruta per defecte
$urlParts = explode('/', $url);

// Assigna controlador i acció
$controllerName = 'app\\controlador\\' . ucfirst($urlParts[0] . 'Controller');
$action = $urlParts[1] ?? 'index';

// Casos especials per a rutes amigables
if ($url === 'login') {
    $controllerName = 'app\\controlador\\AuthController';
    $action = 'login';
} elseif ($url === 'register') {
    $controllerName = 'app\\controlador\\AuthController';
    $action = 'register';
} elseif ($url === 'logout') {
    $controllerName = 'app\\controlador\\AuthController';
    $action = 'logout';
} elseif ($url === 'PrincipalController') {
    $controllerName = 'app\\controlador\\PrincipalController';
    $action = 'index';
}

// Verifica i executa
if (class_exists($controllerName)) {
    $controller = new $controllerName();
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        http_response_code(404);
        echo "Acció no trobada: $action";
    }
} else {
    http_response_code(404);
    echo "Controlador no trobat: $controllerName";
}