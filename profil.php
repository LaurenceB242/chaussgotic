<?php
$user = fetchOne($bdd, "SELECT * FROM utilisateurs WHERE id = ?", [$_SESSION['user']['id']]);
if (!$user) {
    header('Location: deconnexion.php');
    exit;
}

$stats = fetchOne($bdd, "
    SELECT COUNT(*) AS nb, COALESCE(SUM(total), 0) AS depense
    FROM commandes WHERE id_utilisateur = ? AND statut <> 'annulee'", [$user['id']]);

$dernieres = fetchAll($bdd,
    "SELECT id, total, statut, date_commande FROM commandes
     WHERE id_utilisateur = ? ORDER BY id DESC LIMIT 5", [$user['id']]);

$initiale = mb_strtoupper(mb_substr($user['nom'], 0, 1));
$statuts  = ['en_attente' => 'En attente', 'expediee' => 'Expédiée',
             'livree' => 'Livrée', 'annulee' => 'Annulée'];
?>
<section class="profil">

    <header class="profil-entete">
        <div class="avatar"><?= e($initiale) ?></div>
        <div>
            <h1><?= e($user['nom']) ?></h1>
            <p class="profil-mail"><?= e($user['email']) ?></p>
            <span class="badge-role <?= $user['role'] === 'admin' ? 'admin' : '' ?>">
                <?= $user['role'] === 'admin' ? 'Administrateur' : 'Membre' ?>
            </span>
            <small>Inscrit le <?= e(date('d/m/Y', strtotime($user['date_inscription']))) ?></small>
        </div>
    </header>

    <div class="profil-stats">
        <div><strong><?= (int)$stats['nb'] ?></strong><span>Commande(s)</span></div>
        <div><strong><?= number_format((float)$stats['depense'], 2, ',', ' ') ?> €</strong><span>Dépensés</span></div>
        <div><strong><?= nombreArticlesPanier() ?></strong><span>Dans le panier</span></div>
    </div>

    <nav class="onglets">
        <button type="button" class="actif" data-onglet="infos">Mes informations</button>
        <button type="button" data-onglet="mdp">Mot de passe</button>
        <button type="button" data-onglet="cmd">Mes commandes</button>
    </nav>

    <div class="panneau" id="infos">
        <form method="post" action="traitementProfil.php">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="infos">
            <label>Nom
                <input type="text" name="nom" value="<?= e($user['nom']) ?>" maxlength="100" required>
            </label>
            <label>Email
                <input type="email" name="email" value="<?= e($user['email']) ?>" required>
            </label>
            <label>Mot de passe actuel (pour confirmer)
                <input type="password" name="mdp_actuel" autocomplete="current-password" required>
            </label>
            <button type="submit">Enregistrer</button>
        </form>
    </div>

    <div class="panneau" id="mdp" hidden>
        <form method="post" action="traitementProfil.php">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="mdp">
            <label>Mot de passe actuel
                <input type="password" name="mdp_actuel" autocomplete="current-password" required>
            </label>
            <label>Nouveau mot de passe (8 caractères minimum)
                <input type="password" name="nouveau_mdp" minlength="8" autocomplete="new-password" required>
            </label>
            <label>Confirmer le nouveau mot de passe
                <input type="password" name="nouveau_mdp2" minlength="8" autocomplete="new-password" required>
            </label>
            <button type="submit">Changer le mot de passe</button>
        </form>
    </div>

    <div class="panneau" id="cmd" hidden>
        <?php if (empty($dernieres)): ?>
            <p>Tu n'as pas encore passé de commande.</p>
            <a class="bouton-lien" href="index.php?action=chaussures">Découvrir les chaussures</a>
        <?php else: ?>
            <table class="panier">
                <tr><th>N°</th><th>Date</th><th>Statut</th><th>Total</th></tr>
                <?php foreach ($dernieres as $c): ?>
                    <tr>
                        <td>#<?= (int)$c['id'] ?></td>
                        <td><?= e(date('d/m/Y', strtotime($c['date_commande']))) ?></td>
                        <td><span class="statut <?= e($c['statut']) ?>"><?= e($statuts[$c['statut']] ?? $c['statut']) ?></span></td>
                        <td><?= number_format((float)$c['total'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <a class="bouton-lien" href="index.php?action=commandes">Voir le détail de mes commandes</a>
        <?php endif; ?>
    </div>

    <div class="profil-actions">
        <?php if ($user['role'] === 'admin'): ?>
            <a class="bouton-lien" href="admin/dashboard.php">Ouvrir le dashboard</a>
        <?php endif; ?>
        <form method="post" action="deconnexion.php" style="display:inline">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <button type="submit" class="bouton-danger">Se déconnecter</button>
        </form>
    </div>
</section>

<script>
document.querySelectorAll('.onglets button').forEach(function (b) {
    b.addEventListener('click', function () {
        document.querySelectorAll('.onglets button').forEach(function (x) { x.classList.remove('actif'); });
        document.querySelectorAll('.panneau').forEach(function (p) { p.hidden = true; });
        b.classList.add('actif');
        document.getElementById(b.dataset.onglet).hidden = false;
    });
});
var o = new URLSearchParams(location.search).get('onglet');
if (o) { var btn = document.querySelector('[data-onglet="' + o + '"]'); if (btn) btn.click(); }
</script>
