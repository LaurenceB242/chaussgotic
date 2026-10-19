<?php
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

$titre = $id ? 'Modifier une marque' : 'Ajouter une marque';
require 'partials/header.php';
?>
<h1 class="h3 mb-4"><?= e($titre) ?></h1>

<div class="card"><div class="card-body">
<form method="post" action="treatmentMarque.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="action" value="<?= $id ? 'update' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="mb-3">
        <label class="form-label">Nom</label>
        <input class="form-control" type="text" name="nom" value="<?= e($marque['nom']) ?>" maxlength="100" required>
    </div>

    <?php if ($marque['logo']): ?>
        <p>Logo actuel : <img class="vignette" src="../images/marques/<?= e($marque['logo']) ?>" alt=""></p>
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Logo <?= $id ? "(laisser vide pour garder l'actuel)" : '' ?></label>
        <input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
    </div>

    <button class="btn btn-primary" type="submit">Enregistrer</button>
    <a class="btn btn-outline-light" href="marques.php">Annuler</a>
</form>
</div></div>
<?php require 'partials/footer.php'; ?>
