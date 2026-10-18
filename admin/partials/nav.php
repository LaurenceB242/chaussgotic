<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="marques.php">Marques</a>
      <a href="chaussures.php">Chaussures</a>
      <a href="pointures.php">Pointures</a>
    <a href="../index.php">Voir le site</a>
    <a href="../deconnexion.php">Déconnexion</a>
</nav>
<?php if ($f = lireFlash()): ?>
    <p class="<?= e($f['type']) ?>"><?= e($f['message']) ?></p>
<?php endif; ?>