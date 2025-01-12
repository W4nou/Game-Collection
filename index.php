<?php
// Activer l'affichage des erreurs (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarre la session
session_start();

// Chargement des dépendances
require_once 'config/Database.php';

// Récupération du paramètre 'url' qui contient l'URL réécrite
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$params = explode('/', $url);

// Détermine le contrôleur et l'action à partir de l'URL
$controllerName = !empty($params[0]) ? ucfirst($params[0]) . 'Controller' : 'HomeController';
$actionName = !empty($params[1]) ? $params[1] : 'display';

// Instancier la connexion à la base de données
$pdo = Database::getConnection();

// Chemin vers le fichier du contrôleur
$controllerFile = 'controllers/' . $controllerName . '.php';

// Vérifier si le contrôleur existe
if (file_exists($controllerFile)) {
    require_once $controllerFile;

    // Vérification spéciale pour ProfileController, qui attend un userId
    if ($controllerName === 'ProfileController') {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $controller = new $controllerName($pdo, $userId);
        } else {
            // Redirigez vers la page de connexion si l'utilisateur n'est pas connecté
            header('Location: /login');
            exit;
        }
    } else {
        // Instancier les autres contrôleurs avec seulement le $pdo
        $controller = new $controllerName($pdo);
    }

    // Vérifier si l'action (méthode) existe dans le contrôleur
    if (method_exists($controller, $actionName)) {
        // Appeler l'action avec les paramètres restants
        $controller->$actionName(array_slice($params, 2));
    } else {
        // Action introuvable
        http_response_code(404);
        echo "L'action $actionName n'a pas été trouvée dans le contrôleur $controllerName.";
    }
} else {
    // Contrôleur introuvable
    http_response_code(404);
    echo "Le contrôleur $controllerName est introuvable.";
}
