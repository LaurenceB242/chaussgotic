<?php
require_once '../assets/functions.php';
exigerAdmin();

$id = (int)($_GET['id'] ?? 0);
$chaussure = ['nom' => '', 'description' => '', 'prix' => '', 'id_marque' => 0];
$images = [];
$stockActuel = [];

if ($id > 0) {
    $chaussure = fetchOne($bdd, "SELECT * FROM chaussures WHERE id = ?", [$id]);
    if (!$chaussure) {
        flash("Chaussure introuvable.", 'erreur');
        header('Location: chaussures.php');
        exit;
    }
    $images = fetchAll($bdd,
        "SELECT id, path FROM images WHERE id_chaussure = ? ORDER BY date_ajout ASC, id ASC", [$id]);

    foreach (fetchAll($bdd, "SELECT id_pointure, quantite FROM stock WHERE id_chaussure = ?", [$id]) as $s) {
        $stockActuel[$s['id_pointure']] = $s['quantite'];
    }
}

if (isset($_SESSION['old'])) {
    $chaussure = array_merge($chaussure, $_SESSION['old']);
    unset($_SESSION['old']);
}

$marques   = fetchAll($bdd, "SELECT id, nom FROM marques ORDER BY nom");
$pointures = fetchAll($bdd, "SELECT id, valeur FROM pointures ORDER BY valeur");

$titre = $id ? 'Modifier une chaussure' : 'Ajouter une chaussure';
require 'partials/header.php';
?>
<h1 class="h3 mb-4"><?= e($titre) ?></h1>

<?php if (empty($marques)): ?>
    <div class="alert alert-warning">Crée d'abord une <a href="formMarque.php">marque</a>.</div>
<?php else: ?>

<form method="post" action="treatmentChaussure.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="action" value="<?= $id ? 'update' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="card mb-4"><div class="card-body">
        <h2 class="h5 mb-3">Informations</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input class="form-control" type="text" name="nom" value="<?= e($chaussure['nom']) ?>" maxlength="150" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Marque</label>
                <select class="form-select" name="id_marque" required>
                    <option value="">Choisir…</option>
                    <?php foreach ($marques as $m): ?>
                        <option value="<?= (int)$m['id'] ?>"
                            <?= (int)$chaussure['id_marque'] === (int)$m['id'] ? 'selected' : '' ?>>
                            <?= e($m['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Prix (€)</label>
                <input class="form-control" type="text" name="prix" inputmode="decimal" value="<?= e($chaussure['prix']) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4"><?= e($chaussure['description']) ?></textarea>
            </div>
        </div>
    </div></div>

    <div class="card mb-4"><div class="card-body">
        <h2 class="h5 mb-3">Images</h2>
        <?php if ($images): ?>
            <p class="text-secondary">Images actuelles (coche pour supprimer) :</p>
            <div class="d-flex flex-wrap gap-3 mb-3">
                <?php foreach ($images as $img): ?>
                    <label class="text-center">
                        <img class="d-block mb-1" style="width:90px;height:90px;object-fit:cover"
                             src="../images/chaussures/<?= e($img['path']) ?>" alt="">
                        <input class="form-check-input" type="checkbox" name="suppr_images[]" value="<?= (int)$img['id'] ?>">
                        Supprimer
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <label class="form-label">Ajouter des images (JPG, PNG, WebP, 2 Mo max chacune)</label>
        <input class="form-control" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
    </div></div>

    <div class="card mb-4"><div class="card-body">
        <h2 class="h5 mb-3">Stock par pointure</h2>
        <?php if (empty($pointures)): ?>
            <p class="text-secondary">Aucune pointure. <a href="pointures.php">Ajoute-en d'abord.</a></p>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($pointures as $p): ?>
                <div class="col-4 col-md-2">
                    <label class="form-label">
                        <?= rtrim(rtrim(number_format((float)$p['valeur'], 1, '.', ''), '0'), '.') ?>
                    </label>
                    <input class="form-control" type="number" name="stock[<?= (int)$p['id'] ?>]"
                           min="0" max="9999" value="<?= (int)($stockActuel[$p['id']] ?? 0) ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div></div>

    <button class="btn btn-primary" type="submit">Enregistrer</button>
    <a class="btn btn-outline-light" href="chaussures.php">Annuler</a>
</form>

<?php endif; ?>
<?php require 'partials/footer.php'; ?>
