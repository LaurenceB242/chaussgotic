<?php
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

$titre = 'Chaussures';
require 'partials/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 m-0">Chaussures</h1>
    <a class="btn btn-primary" href="formChaussure.php">+ Ajouter une chaussure</a>
</div>

<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle m-0">
    <thead><tr>
        <th>Image</th><th>Nom</th><th>Marque</th><th>Prix</th><th>Images</th><th>Stock</th>
        <th class="text-end">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($chaussures as $c): ?>
        <tr>
            <td>
                <?php if ($c['image']): ?>
                    <img class="vignette" src="../images/chaussures/<?= e($c['image']) ?>" alt="<?= e($c['nom']) ?>">
                <?php endif; ?>
            </td>
            <td><?= e($c['nom']) ?></td>
            <td><?= e($c['marque']) ?></td>
            <td><?= number_format((float)$c['prix'], 2, ',', ' ') ?> €</td>
            <td><?= (int)$c['nb_images'] ?></td>
            <td>
                <?php if ((int)$c['stock_total'] > 0): ?>
                    <?= (int)$c['stock_total'] ?>
                <?php else: ?>
                    <span class="badge text-bg-danger">Rupture</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-light" href="formChaussure.php?id=<?= (int)$c['id'] ?>">Modifier</a>
                <form method="post" action="treatmentChaussure.php" class="d-inline"
                      onsubmit="return confirm('Supprimer cette chaussure, ses images et son stock ?');">
                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($chaussures)): ?>
        <tr><td colspan="7" class="text-center text-secondary py-4">Aucune chaussure pour l'instant.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div></div>
<?php require 'partials/footer.php'; ?>
