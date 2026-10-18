<?php
require_once 'assets/functions.php';

$pages = [
    'home'       => 'pages/home.php',
    'chaussures' => 'pages/chaussures.php',
    'chaussure'  => 'pages/chaussure.php',
    'marque'     => 'pages/marque.php',
    'panier'     => 'pages/panier.php',
    'commandes'  => 'pages/commandes.php',
];
$protegees = ['commandes'];            // pages réservées aux connectés

$action = $_GET['action'] ?? 'home';
$fichier = $pages[$action] ?? null;

if ($fichier === null) {
    http_response_code(404);
    $fichier = 'pages/404.php';
} elseif (in_array($action, $protegees, true)) {
    exigerConnexion();
}

$marquesNav = fetchAll($bdd, "SELECT id, nom FROM marques ORDER BY nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>chaussGotic</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dentelle dentelle-gauche"></div>
<div class="dentelle dentelle-droite"></div>

<?php require 'partials/nav.php'; ?>

<main>
    <?php if ($f = lireFlash()): ?>
        <p class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></p>
    <?php endif; ?>

    <?php require $fichier; ?>
</main>
</body>
</html>