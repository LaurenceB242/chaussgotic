<?php
require_once '../assets/functions.php';
exigerAdmin();

$nbChaussures = fetchOne($bdd, "SELECT COUNT(*) AS n FROM chaussures")['n'];
$nbMarques    = fetchOne($bdd, "SELECT COUNT(*) AS n FROM marques")['n'];
$nbMembres    = fetchOne($bdd, "SELECT COUNT(*) AS n FROM utilisateurs WHERE role = 'membre'")['n'];
$stockTotal   = fetchOne($bdd, "SELECT COALESCE(SUM(quantite),0) AS n FROM stock")['n'];
$ruptures     = fetchOne($bdd, "
    SELECT COUNT(*) AS n FROM chaussures c
    WHERE COALESCE((SELECT SUM(quantite) FROM stock WHERE id_chaussure = c.id), 0) = 0")['n'];

$titre = 'Dashboard';
require 'partials/header.php';
?>
<h1 class="h3 mb-4">Bienvenue <?= e($_SESSION['user']['nom']) ?></h1>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['Chaussures', $nbChaussures, 'chaussures.php'],
        ['Marques',    $nbMarques,    'marques.php'],
        ['Membres',    $nbMembres,    null],
        ['Paires en stock', $stockTotal, 'chaussures.php'],
        ['En rupture', $ruptures,     'chaussures.php'],
    ] as [$label, $valeur, $lien]): ?>
        <div class="col-6 col-md-4 col-lg">
            <div class="card h-100"><div class="card-body text-center">
                <div class="stat-nombre"><?= (int)$valeur ?></div>
                <div class="text-secondary"><?= e($label) ?></div>
                <?php if ($lien): ?><a href="<?= $lien ?>" class="stretched-link"></a><?php endif; ?>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="d-flex gap-2">
    <a class="btn btn-outline-light" href="formChaussure.php">+ Chaussure</a>
    <a class="btn btn-outline-light" href="formMarque.php">+ Marque</a>
    <a class="btn btn-outline-light" href="pointures.php">Pointures</a>
</div>
<?php require 'partials/footer.php'; ?>