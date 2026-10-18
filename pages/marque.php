<?php
$id = (int)($_GET['id'] ?? 0);
$marque = fetchOne($bdd, "SELECT * FROM marques WHERE id = ?", [$id]);

if (!$marque) {
    echo '<p>Marque introuvable.</p>';
    return;
}

$chaussures = fetchAll($bdd, "
    SELECT c.id, c.nom, c.prix, m.nom AS marque,
        (SELECT path FROM images WHERE id_chaussure = c.id
         ORDER BY date_ajout ASC, id ASC LIMIT 1) AS image
    FROM chaussures c
    JOIN marques m ON m.id = c.id_marque
    WHERE c.id_marque = ?
    ORDER BY c.id DESC
", [$id]);
?>
<header class="entete-marque">
    <?php if ($marque['logo']): ?>
        <img src="images/marques/<?= e($marque['logo']) ?>" alt="<?= e($marque['nom']) ?>" height="60">
    <?php endif; ?>
    <h1><?= e($marque['nom']) ?></h1>
</header>

<section class="chaussures-home">
    <?php foreach ($chaussures as $c) carteChaussure($c); ?>
</section>

<?php if (empty($chaussures)): ?>
    <p>Aucune chaussure pour cette marque.</p>
<?php endif; ?>