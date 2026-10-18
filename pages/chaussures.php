<?php
$tris = [
    'recent'   => 'c.id DESC',
    'prix_asc' => 'c.prix ASC',
    'prix_desc'=> 'c.prix DESC',
    'nom'      => 'c.nom ASC',
];
$tri = $_GET['tri'] ?? 'recent';
$ordre = $tris[$tri] ?? $tris['recent'];   // liste blanche : jamais la valeur de l'URL directement

$chaussures = fetchAll($bdd, "
    SELECT c.id, c.nom, c.prix, m.nom AS marque,
        (SELECT path FROM images WHERE id_chaussure = c.id
         ORDER BY date_ajout ASC, id ASC LIMIT 1) AS image
    FROM chaussures c
    JOIN marques m ON m.id = c.id_marque
    ORDER BY $ordre
");
?>
<h1>Toutes les chaussures</h1>

<form method="get" class="tri">
    <input type="hidden" name="action" value="chaussures">
    <label>Trier par
        <select name="tri" onchange="this.form.submit()">
            <option value="recent"    <?= $tri === 'recent' ? 'selected' : '' ?>>Nouveautés</option>
            <option value="prix_asc"  <?= $tri === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
            <option value="prix_desc" <?= $tri === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            <option value="nom"       <?= $tri === 'nom' ? 'selected' : '' ?>>Nom</option>
        </select>
    </label>
</form>

<section class="chaussures-home">
    <?php foreach ($chaussures as $c) carteChaussure($c); ?>
</section>

<?php if (empty($chaussures)): ?>
    <p>Aucune chaussure pour l'instant.</p>
<?php endif; ?>