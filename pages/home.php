<?php
$chaussures = fetchAll($bdd, "
    SELECT c.id, c.nom, m.nom AS marque, i.path AS image
    FROM chaussures c
    JOIN marques m ON m.id = c.id_marque
    LEFT JOIN images i ON i.id = (
        SELECT id FROM images
        WHERE id_chaussure = c.id
        ORDER BY date_ajout ASC, id ASC
        LIMIT 1
    )
    ORDER BY c.id DESC
    LIMIT 8
");

$haut = array_slice($chaussures, 0, 4);
$bas  = array_slice($chaussures, 4, 4);

function carteChauss(array $c): void { ?>
    <a href="index.php?action=chaussure&id=<?= (int)$c['id'] ?>" class="cards">
        <div class="cover">
            <?php if ($c['image']): ?>
                <img src="images/chaussures/<?= e($c['image']) ?>" alt="<?= e($c['nom']) ?>">
            <?php endif; ?>
        </div>
        <div class="title"><?= e($c['nom']) ?></div>
        <div class="marque"><?= e($c['marque']) ?></div>
    </a>
<?php } ?>

<section class="chaussures-home">
    <?php foreach ($haut as $c) carteChauss($c); ?>
</section>

<section class="bandeau">
    <h2>Nouveauté !</h2>
    <p>Découvrez les dernières arrivées</p>
</section>

<section class="chaussures-home">
    <?php foreach ($bas as $c) carteChauss($c); ?>
</section>

<?php if (empty($chaussures)): ?>
    <p>Aucune chaussure pour l'instant.</p>
<?php endif; ?>