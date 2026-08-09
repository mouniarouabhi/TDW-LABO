<?php
// index.php - Routeur Principal avec vues en classes
session_start();

// Autoloader pour modèles, contrôleurs et vues (classes)
spl_autoload_register(function($class) {
    $paths = [
        "models/$class.php",
        "controllers/$class.php",
        "views/$class.php",
        "views/layout/$class.php",
        "views/public/$class.php",
        "views/admin/$class.php",
        "views/components/$class.php"
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});


// Gestion des erreurs en développement (désactiver en prod)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Router simple basé sur l'URL
$url = $_GET['url'] ?? 'home';
$url = explode('/', filter_var($url, FILTER_SANITIZE_URL));

$controllerName = ucfirst($url[0]) . 'Controller';
$method = $url[1] ?? 'index';
$params = array_slice($url, 2);

if (file_exists("controllers/$controllerName.php")) {
    try {
        $controller = new $controllerName();
        if (method_exists($controller, $method)) {
            call_user_func_array([$controller, $method], $params);
        } else {
            http_response_code(404);
            echo "Méthode introuvable";
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "Erreur: " . $e->getMessage();
    }
} else {
    http_response_code(404);
    echo "Contrôleur introuvable";
}
?>