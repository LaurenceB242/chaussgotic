<?php
$titre = 'Chaussures'; require 'partials/header.php';
require_once '../assets/functions.php';
exigerAdmin();

$pointures = fetchAll($bdd, "
    SELECT p.id, p.valeur,
           COUNT(s.id) AS nb_chaussures,
           COALESCE(SUM(s.quantite), 0) AS stock_total
    FROM pointures p
    LEFT JOIN stock s ON s.id_pointure = p.id
    GROUP BY p.id
    ORDER BY p.valeur
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pointures</title>
</head>
<body>
<?php require 'partials/nav.php'; ?>

<h1>Pointures</h1>

<h2>Ajouter</h2>
<form method="post" action="treatmentPointure.php">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="action" value="add">
    <label>Pointure (ex. 42 ou 42.5)
        <input type="text" name="valeur" inputmode="decimal" required style="width:6em">
    </label>
    <button type="submit">Ajouter</button>
</form>

<h2>Ajouter une série</h2>
<form method="post" action="treatmentPointure.php">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="action" value="add_serie">
    <label>De <input type="number" name="debut" min="15" max="60" value="36" style="width:5em"></label>
    <label>à <input type="number" name="fin" min="15" max="60" value="46" style="width:5em"></label>
    <label><input type="checkbox" name="demi" value="1"> avec les demi-pointures</label>
    <button type="submit">Ajouter la série</button>
</form>

<h2>Liste</h2>
<table>
    <tr><th>Pointure</th><th>Chaussures concernées</th><th>Stock total</th><th>Action</th></tr>
    <?php foreach ($pointures as $p): ?>
    <tr>
        <td><?= rtrim(rtrim(number_format((float)$p['valeur'], 1, '.', ''), '0'), '.') ?></td>
        <td><?= (int)$p['nb_chaussures'] ?></td>
        <td><?= (int)$p['stock_total'] ?></td>
        <td>
            <form method="post" action="treatmentPointure.php" style="display:inline"
                  onsubmit="return confirm('Supprimer cette pointure ?');">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($pointures)): ?>
        <tr><td colspan="4">Aucune pointure pour l'instant.</td></tr>
    <?php endif; ?>
</table>
<?php require 'partials/footer.php'; ?>