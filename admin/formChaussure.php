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

// Si une erreur a eu lieu, on réaffiche ce qui avait été saisi
if (isset($_SESSION['old'])) {
    $chaussure = array_merge($chaussure, $_SESSION['old']);
    unset($_SESSION['old']);
}

$marques   = fetchAll($bdd, "SELECT id, nom FROM marques ORDER BY nom");
$pointures = fetchAll($bdd, "SELECT id, valeur FROM pointures ORDER BY valeur");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Modifier' : 'Ajouter' ?> une chaussure</title>
</head>
<body>
<?php require 'partials/nav.php'; ?>

<h1><?= $id ? 'Modifier' : 'Ajouter' ?> une chaussure</h1>

<?php if (empty($marques)): ?>
    <p>Crée d'abord une <a href="formMarque.php">marque</a>.</p>
<?php else: ?>

<form method="post" action="treatmentChaussure.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="action" value="<?= $id ? 'update' : 'add' ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Nom
        <input type="text" name="nom" value="<?= e($chaussure['nom']) ?>" maxlength="150" required>
    </label>

    <label>Marque
        <select name="id_marque" required>
            <option value="">Choisir…</option>
            <?php foreach ($marques as $m): ?>
                <option value="<?= (int)$m['id'] ?>"
                    <?= (int)$chaussure['id_marque'] === (int)$m['id'] ? 'selected' : '' ?>>
                    <?= e($m['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Prix (€)
        <input type="text" name="prix" inputmode="decimal" value="<?= e($chaussure['prix']) ?>" required>
    </label>

    <label>Description
        <textarea name="description" rows="5"><?= e($chaussure['description']) ?></textarea>
    </label>

    <h2>Images</h2>
    <?php if ($images): ?>
        <p>Images actuelles (coche pour supprimer) :</p>
        <?php foreach ($images as $img): ?>
            <label>
                <img src="../images/chaussures/<?= e($img['path']) ?>" alt="" width="90">
                <input type="checkbox" name="suppr_images[]" value="<?= (int)$img['id'] ?>"> Supprimer
            </label>
        <?php endforeach; ?>
    <?php endif; ?>

    <label>Ajouter des images (JPG, PNG, WebP, 2 Mo max chacune)
        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
    </label>

    <h2>Stock par pointure</h2>
    <?php foreach ($pointures as $p): ?>
        <label>
            <?= rtrim(rtrim(number_format((float)$p['valeur'], 1, '.', ''), '0'), '.') ?>
            <input type="number" name="stock[<?= (int)$p['id'] ?>]" min="0" max="9999"
                   value="<?= (int)($stockActuel[$p['id']] ?? 0) ?>" style="width:5em">
        </label>
    <?php endforeach; ?>

    <p><button type="submit">Enregistrer</button> <a href="chaussures.php">Annuler</a></p>
</form>

<?php endif; ?>
</body>
</html>