<?php
$pageCourante = basename($_SERVER['SCRIPT_NAME']);
$liens = [
    'dashboard.php'  => 'Dashboard',
    'marques.php'    => 'Marques',
    'chaussures.php' => 'Chaussures',
    'pointures.php'  => 'Pointures',
];
// les pages formulaire activent le menu de leur liste
$actif = ['formMarque.php' => 'marques.php', 'formChaussure.php' => 'chaussures.php'][$pageCourante] ?? $pageCourante;
$flash = lireFlash();
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Admin') ?> · chaussGotic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg border-bottom border-secondary sticky-top admin-nav">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../images/logo/gothrose.svg" alt="" height="32"> Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuAdmin">
            <ul class="navbar-nav me-auto">
                <?php foreach ($liens as $fichier => $label): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $actif === $fichier ? 'active' : '' ?>" href="<?= $fichier ?>"><?= $label ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-sm btn-outline-light me-2" href="../profil.php">Mon profil</a>
            <a class="btn btn-sm btn-outline-light me-2" href="../index.php">Voir le site</a>
            <form method="post" action="../deconnexion.php" class="d-inline">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Déconnexion</button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4">
<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'erreur' ? 'danger' : 'success' ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>