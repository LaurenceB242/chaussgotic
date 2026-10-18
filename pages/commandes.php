<?php
$commandes = fetchAll($bdd,
    "SELECT * FROM commandes WHERE id_utilisateur = ? ORDER BY id DESC",
    [$_SESSION['user']['id']]);
?>
<h1>Mes commandes</h1>

<?php foreach ($commandes as $cmd):
    $lignes = fetchAll($bdd, "SELECT * FROM commande_lignes WHERE id_commande = ?", [$cmd['id']]); ?>
    <section class="commande">
        <h2>Commande n°<?= (int)$cmd['id'] ?>
            <small>du <?= e(date('d/m/Y H:i', strtotime($cmd['date_commande']))) ?>
            , <?= e(str_replace('_', ' ', $cmd['statut'])) ?></small></h2>
        <ul>
            <?php foreach ($lignes as $l): ?>
                <li><?= (int)$l['quantite'] ?> × <?= e($l['nom']) ?>
                    (pointure <?= rtrim(rtrim(number_format((float)$l['pointure'], 1, '.', ''), '0'), '.') ?>)
                    : <?= number_format($l['prix_unitaire'] * $l['quantite'], 2, ',', ' ') ?> €</li>
            <?php endforeach; ?>
        </ul>
        <p>Total : <strong><?= number_format((float)$cmd['total'], 2, ',', ' ') ?> €</strong></p>
    </section>
<?php endforeach; ?>

<?php if (empty($commandes)): ?>
    <p>Tu n'as pas encore passé de commande.</p>
<?php endif; ?>