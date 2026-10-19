<?php
require_once '../assets/functions.php';
exigerAdmin();

$marques = fetchAll($bdd, "
    SELECT m.*, COUNT(c.id) AS nb_chaussures
    FROM marques m
    LEFT JOIN chaussures c ON c.id_marque = m.id
    GROUP BY m.id
    ORDER BY m.nom
");

$titre = 'Marques';
require 'partials/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 m-0">Marques</h1>
    <a class="btn btn-primary" href="formMarque.php">+ Ajouter une marque</a>
</div>

<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle m-0">
    <thead><tr><th>Logo</th><th>Nom</th><th>Chaussures</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($marques as $m): ?>
        <tr>
            <td>
                <?php if ($m['logo']): ?>
                    <img class="vignette" src="../images/marques/<?= e($m['logo']) ?>" alt="<?= e($m['nom']) ?>">
                <?php endif; ?>
            </td>
            <td><?= e($m['nom']) ?></td>
            <td><?= (int)$m['nb_chaussures'] ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-light" href="formMarque.php?id=<?= (int)$m['id'] ?>">Modifier</a>
                <form method="post" action="treatmentMarque.php" class="d-inline"
                      onsubmit="return confirm('Supprimer cette marque ?');">
                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($marques)): ?>
        <tr><td colspan="4" class="text-center text-secondary py-4">Aucune marque pour l'instant.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div></div>
<?php require 'partials/footer.php'; ?>
