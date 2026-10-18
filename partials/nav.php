<nav class="navbar">
   <a href="index.php" class="logo">
    <img src="images/logo/gothrose.svg" alt="logo">
</a>
    <div class="menu">
        <a href="index.php?action=chaussures">Chaussures</a>

        <div class="dropdown">
            <a href="#">Marques ▾</a>
            <div class="dropdown-contenu">
                <?php foreach ($marquesNav as $m): ?>
                    <a href="index.php?action=marque&id=<?= (int)$m['id'] ?>"><?= e($m['nom']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <a href="index.php?action=panier">Panier (<?= nombreArticlesPanier() ?>)</a>

        <?php if (estConnecte()): ?>
            <a href="index.php?action=commandes">Mes commandes</a>
            <a href="profil.php"><?= e($_SESSION['user']['nom']) ?></a>
            <?php if (estAdmin()): ?><a href="admin/dashboard.php">Admin</a><?php endif; ?>
            <a href="deconnexion.php">Déconnexion</a>
        <?php else: ?>
            <a href="connexion.php">Connexion</a>
        <?php endif; ?>
    </div>
</nav>