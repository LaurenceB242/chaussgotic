<?php
$id = (int)($_GET['id'] ?? 0);
$c = fetchOne($bdd, "
    SELECT c.*, m.nom AS marque, m.id AS id_marque
    FROM chaussures c JOIN marques m ON m.id = c.id_marque
    WHERE c.id = ?", [$id]);

if (!$c) {
    echo '<p>Chaussure introuvable.</p>';
    return;
}

$images = fetchAll($bdd,
    "SELECT path FROM images WHERE id_chaussure = ? ORDER BY date_ajout ASC, id ASC", [$id]);

$tailles = fetchAll($bdd, "
    SELECT p.id, p.valeur, s.quantite
    FROM stock s JOIN pointures p ON p.id = s.id_pointure
    WHERE s.id_chaussure = ?
    ORDER BY p.valeur", [$id]);

$enStock = array_sum(array_column($tailles, 'quantite')) > 0;
?>
<article class="fiche">
    <div class="galerie">
        <?php if ($images): ?>
            <img id="image-principale" src="images/chaussures/<?= e($images[0]['path']) ?>"
                 alt="<?= e($c['nom']) ?>">
            <?php if (count($images) > 1): ?>
                <div class="miniatures">
                    <?php foreach ($images as $img): ?>
                        <img src="images/chaussures/<?= e($img['path']) ?>" alt=""
                             onclick="document.getElementById('image-principale').src = this.src">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Pas d'image.</p>
        <?php endif; ?>
    </div>

    <div class="infos">
        <p><a href="index.php?action=marque&id=<?= (int)$c['id_marque'] ?>"><?= e($c['marque']) ?></a></p>
        <h1><?= e($c['nom']) ?></h1>
        <p class="prix"><?= number_format((float)$c['prix'], 2, ',', ' ') ?> €</p>
        <p><?= nl2br(e($c['description'])) ?></p>

        <?php if (!$enStock): ?>
            <p class="rupture">Rupture de stock</p>
        <?php else: ?>
            <form method="post" action="traitementPanier.php">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id_chaussure" value="<?= (int)$c['id'] ?>">

                <p>Pointure :</p>
                <div class="tailles">
                    <?php foreach ($tailles as $t):
                        $dispo = (int)$t['quantite'] > 0; ?>
                        <label class="taille <?= $dispo ? '' : 'indisponible' ?>">
                            <input type="radio" name="id_pointure" value="<?= (int)$t['id'] ?>"
                                   <?= $dispo ? 'required' : 'disabled' ?>>
                            <span><?= rtrim(rtrim(number_format((float)$t['valeur'], 1, '.', ''), '0'), '.') ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <label>Quantité
                    <input type="number" name="quantite" value="1" min="1" max="10" style="width:5em">
                </label>
                <button type="submit">Ajouter au panier</button>
            </form>
        <?php endif; ?>
    </div>
</article>