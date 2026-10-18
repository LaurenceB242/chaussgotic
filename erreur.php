<?php
require_once 'assets/functions.php';

$erreurs = [
    400 => ['Requête invalide',   "La requête envoyée n'est pas comprise par le serveur."],
    403 => ['Accès refusé',       "Tu n'as pas le droit d'accéder à cette page."],
    404 => ['Page introuvable',   "Cette page s'est perdue dans les ténèbres."],
    500 => ['Erreur du serveur',  "Quelque chose s'est mal passé de notre côté. Réessaie plus tard."],
];

$code = (int)($_GET['code'] ?? 404);
if (!isset($erreurs[$code])) {
    $code = 404;
}
http_response_code($code);
[$titre, $message] = $erreurs[$code];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $code ?> : <?= e($titre) ?></title>
    <link rel="stylesheet" href="/webdev/sitechaussgotic/assets/css/style.css">
</head>
<body>
<div class="dentelle dentelle-gauche"></div>
<div class="dentelle dentelle-droite"></div>

<main class="centre">
    <h1><?= $code ?></h1>
    <h2><?= e($titre) ?></h2>
    <p><?= e($message) ?></p>
    <a href="/webdev/sitechaussgotic/index.php">Retour à l'accueil</a>
</main>
</body>
</html>