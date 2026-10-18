<?php
$lignes = panierLignes($bdd);
$total = array_sum(array_column($lignes, 'sous_total'));
$probleme = false;
?>
<h1>Mon panier</h1>

<?php if (empty($lignes)): ?>
    <p>Ton panier est vide.</p>
    <a href="index.php?action=chaussures">Voir les chaussures</a>
<?php else: ?>

<table class="panier">
    <tr><th></th><th>Chaussure</th><th>Pointure</th><th>Prix</th><th>Quantité</th><th>Total</th><th></th></tr>
    <?php foreach ($lignes as $l):
        if ($l['quantite'] > $l['stock']) $probleme = true; ?>
    <tr>
        <td>
            <?php if ($l['image']): ?>
                <img src="images/chaussures/<?= e($l['image']) ?>" alt="" width="60">
            <?php endif; ?>
        </td>
        <td><a href="index.php?action=chaussure&id=<?= (int)$l['id'] ?>"><?= e($l['nom']) ?></a></td>
        <td><?= rtrim(rtrim(number_format((float)$l['pointure'], 1, '.', ''), '0'), '.') ?></td>
        <td><?= number_format((float)$l['prix'], 2, ',', ' ') ?> €</td>
        <td>
            <form method="post" action="traitementPanier.php">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="cle" value="<?= e($l['cle']) ?>">
                <input type="number" name="quantite" value="<?= (int)$l['quantite'] ?>"
                       min="0" max="<?= max(1, (int)$l['stock']) ?>" style="width:4.5em">
                <button type="submit">OK</button>
            </form>
            <?php if ($l['quantite'] > $l['stock']): ?>
                <small class="erreur">Stock restant : <?= (int)$l['stock'] ?></small>
            <?php endif; ?>
        </td>
        <td><?= number_format($l['sous_total'], 2, ',', ' ') ?> €</td>
        <td>
            <form method="post" action="traitementPanier.php">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="cle" value="<?= e($l['cle']) ?>">
                <button type="submit">Retirer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<p class="total">Total : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong></p>

<form method="post" action="traitementPanier.php" style="display:inline">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <input type="hidden" name="action" value="clear">
    <button type="submit">Vider le panier</button>
</form>

<?php if (estConnecte()): ?>
    <form method="post" action="commande.php" style="display:inline">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <button type="submit" <?= $probleme ? 'disabled' : '' ?>>Valider la commande</button>
    </form>
<?php else: ?>
    <p><a href="connexion.php">Connecte-toi</a> ou <a href="inscription.php">crée un compte</a> pour commander.
       Ton panier est conservé.</p>
<?php endif; ?>

<?php endif; ?>