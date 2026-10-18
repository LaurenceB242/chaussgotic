<?php
$titre = 'Chaussures'; require 'partials/header.php';
require_once '../assets/functions.php';
exigerAdmin();

$id = (int)($_GET['id'] ?? 0);
$marque = ['nom' => '', 'logo' => null];

if ($id > 0) {
    $marque = fetchOne($bdd, "SELECT * FROM marques WHERE id = ?", [$id]);
    if (!$marque) {
        flash("Marque introuvable.", 'erreur');
        header('Location: marques.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Modifier' : 'Ajouter' ?> une marque</title>
</head>
<body>
<?php require 'partials/nav.php'; ?>

<h1><?= $id ? 'Modifier' : 'Ajouter' ?> une marque</h1>

<form method="post" action="treatmentMarque.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="action" value="<?= $id ? 'update' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Nom
        <input type="text" name="nom" value="<?= e($marque['nom']) ?>" maxlength="100" required>
    </label>

    <?php if ($marque['logo']): ?>
        <p>Logo actuel : <img src="../images/marques/<?= e($marque['logo']) ?>" alt="" width="60"></p>
    <?php endif; ?>

    <label>Logo <?= $id ? '(laisser vide pour garder l\'actuel)' : '' ?>
        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
    </label>

    <button type="submit">Enregistrer</button>
    <a href="marques.php">Annuler</a>
</form>
<?php require 'partials/footer.php'; ?>