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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marques</title>
</head>
<body>
<?php require 'partials/nav.php'; ?>

<h1>Marques</h1>
<a href="formMarque.php">+ Ajouter une marque</a>

<table>
    <tr><th>Logo</th><th>Nom</th><th>Chaussures</th><th>Actions</th></tr>
    <?php foreach ($marques as $m): ?>
    <tr>
        <td>
            <?php if ($m['logo']): ?>
                <img src="../images/marques/<?= e($m['logo']) ?>" alt="<?= e($m['nom']) ?>" width="50">
            <?php endif; ?>
        </td>
        <td><?= e($m['nom']) ?></td>
        <td><?= (int)$m['nb_chaussures'] ?></td>
        <td>
            <a href="formMarque.php?id=<?= (int)$m['id'] ?>">Modifier</a>
            <form method="post" action="treatmentMarque.php" style="display:inline"
                  onsubmit="return confirm('Supprimer cette marque ?');">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($marques)): ?>
        <tr><td colspan="4">Aucune marque pour l'instant.</td></tr>
    <?php endif; ?>
</table>
</body>
</html>