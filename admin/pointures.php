<?php
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

$titre = 'Pointures';
require 'partials/header.php';
?>
<h1 class="h3 mb-4">Pointures</h1>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Ajouter une pointure</h2>
            <form method="post" action="treatmentPointure.php" class="d-flex gap-2">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="add">
                <input class="form-control" type="text" name="valeur" inputmode="decimal" placeholder="42 ou 42.5" required>
                <button class="btn btn-primary" type="submit">Ajouter</button>
            </form>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Ajouter une série</h2>
            <form method="post" action="treatmentPointure.php" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="add_serie">
                <input class="form-control" style="width:90px" type="number" name="debut" min="15" max="60" value="36">
                à
                <input class="form-control" style="width:90px" type="number" name="fin" min="15" max="60" value="46">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="demi" value="1" id="demi">
                    <label class="form-check-label" for="demi">demi-pointures</label>
                </div>
                <button class="btn btn-primary" type="submit">Ajouter la série</button>
            </form>
        </div></div>
    </div>
</div>

<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle m-0">
    <thead><tr><th>Pointure</th><th>Chaussures concernées</th><th>Stock total</th><th class="text-end">Action</th></tr></thead>
    <tbody>
    <?php foreach ($pointures as $p): ?>
        <tr>
            <td><?= rtrim(rtrim(number_format((float)$p['valeur'], 1, '.', ''), '0'), '.') ?></td>
            <td><?= (int)$p['nb_chaussures'] ?></td>
            <td><?= (int)$p['stock_total'] ?></td>
            <td class="text-end">
                <form method="post" action="treatmentPointure.php" class="d-inline"
                      onsubmit="return confirm('Supprimer cette pointure ?');">
                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($pointures)): ?>
        <tr><td colspan="4" class="text-center text-secondary py-4">Aucune pointure pour l'instant.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div></div>
<?php require 'partials/footer.php'; ?>
