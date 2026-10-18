<?php
require_once '../assets/functions.php';
exigerAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard admin</h1>
    <p>Bienvenue <?= e($_SESSION['user']['nom']) ?></p>
    <a href="../deconnexion.php">Déconnexion</a>
</body>
</html>