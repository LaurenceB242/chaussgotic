<?php
require_once 'assets/functions.php';

$erreurs = [];
$nom = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValide($_POST['csrf'] ?? '')) {
        $erreurs[] = "Session expirée, recharge la page.";
    } else {
        $nom   = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';
        $mdp2  = $_POST['mot_de_passe2'] ?? '';

        if ($nom === '')                                $erreurs[] = "Le nom est obligatoire.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'email n'est pas valide.";
        if (strlen($mdp) < 8)                           $erreurs[] = "Le mot de passe doit faire au moins 8 caractères.";
        if ($mdp !== $mdp2)                             $erreurs[] = "Les deux mots de passe ne correspondent pas.";

        if (empty($erreurs)) {
            if (fetchOne($bdd, "SELECT id FROM utilisateurs WHERE email = ?", [$email])) {
                $erreurs[] = "Cet email est déjà utilisé.";
            } else {
                $bdd->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)")
                    ->execute([$nom, $email, hasherMdp($mdp)]);
                header('Location: connexion.php?inscrit=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dentelle dentelle-gauche"></div>
<div class="dentelle dentelle-droite"></div>

<main>
    <section class="auth">
        <a href="index.php"><img class="logo-auth" src="images/logo/gothrose.svg" alt="chaussGotic"></a>
        <h1>Créer un compte</h1>

        <?php foreach ($erreurs as $erreur): ?>
            <p class="flash erreur"><?= e($erreur) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <label>Nom
                <input type="text" name="nom" value="<?= e($nom) ?>" required>
            </label>
            <label>Email
                <input type="email" name="email" value="<?= e($email) ?>" required>
            </label>
            <label>Mot de passe (8 caractères minimum)
                <input type="password" name="mot_de_passe" minlength="8" autocomplete="new-password" required>
            </label>
            <label>Confirmer le mot de passe
                <input type="password" name="mot_de_passe2" minlength="8" autocomplete="new-password" required>
            </label>
            <button type="submit">S'inscrire</button>
        </form>

        <p class="lien">Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
    </section>
</main>
</body>
</html>