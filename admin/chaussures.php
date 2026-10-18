<?php
$titre = 'Chaussures'; require 'partials/header.php';
require_once '../assets/functions.php';
exigerAdmin();

$chaussures = fetchAll($bdd, "
    SELECT c.id, c.nom, c.prix, m.nom AS marque,
        (SELECT COUNT(*) FROM images i WHERE i.id_chaussure = c.id) AS nb_images,
        (SELECT COALESCE(SUM(s.quantite), 0) FROM stock s WHERE s.id_chaussure = c.id) AS stock_total,
        (SELECT i.path FROM images i WHERE i.id_chaussure = c.id
         ORDER BY i.date_ajout ASC, i.id ASC LIMIT 1) AS image
    FROM chaussures c
    JOIN marques m ON m.id = c.id_marque
    ORDER BY c.id DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chaussures</title>
</head>
<body>
<?php require 'partials/nav.php'; ?>

<h1>Chaussures</h1>
<a href="formChaussure.php">+ Ajouter une chaussure</a>

<table>
    <tr>
        <th>Image</th><th>Nom</th><th>Marque</th><th>Prix</th>
        <th>Images</th><th>Stock</th><th>Actions</th>
    </tr>
    <?php foreach ($chaussures as $c): ?>
    <tr>
        <td>
            <?php if ($c['image']): ?>
                <img src="../images/chaussures/<?= e($c['image']) ?>" alt="<?= e($c['nom']) ?>" width="60">
            <?php endif; ?>
        </td>
        <td><?= e($c['nom']) ?></td>
        <td><?= e($c['marque']) ?></td>
        <td><?= number_format((float)$c['prix'], 2, ',', ' ') ?> €</td>
        <td><?= (int)$c['nb_images'] ?></td>
        <td><?= (int)$c['stock_total'] > 0 ? (int)$c['stock_total'] : 'Rupture' ?></td>
        <td>
            <a href="formChaussure.php?id=<?= (int)$c['id'] ?>">Modifier</a>
            <form method="post" action="treatmentChaussure.php" style="display:inline"
                  onsubmit="return confirm('Supprimer cette chaussure, ses images et son stock ?');">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($chaussures)): ?>
        <tr><td colspan="7">Aucune chaussure pour l'instant.</td></tr>
    <?php endif; ?>
</table>
<?php require 'partials/footer.php'; ?>