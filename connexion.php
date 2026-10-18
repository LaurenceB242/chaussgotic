<?php
require_once 'assets/functions.php';

if (estConnecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValide($_POST['csrf'] ?? '')) {
        $erreur = "Session expirée, recharge la page.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';
        $user  = fetchOne($bdd, "SELECT * FROM utilisateurs WHERE email = ?", [$email]);

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            if (mdpAReHasher($user['mot_de_passe'])) {
                $bdd->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?")
                    ->execute([hasherMdp($mdp), $user['id']]);
            }
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'   => $user['id'],
                'nom'  => $user['nom'],
                'role' => $user['role'],
            ];
            header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
            exit;
        }
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dentelle dentelle-gauche"></div>
<div class="dentelle dentelle-droite"></div>

<main>
    <section class="auth">
        <a href="index.php"><img class="logo-auth" src="images/logo/gothrose.svg" alt="chaussGotic"></a>
        <h1>Connexion</h1>

        <?php if (isset($_GET['inscrit'])): ?>
            <p class="flash succes">Compte créé, tu peux te connecter.</p>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <p class="flash erreur"><?= e($erreur) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <label>Email
                <input type="email" name="email" autocomplete="username" required>
            </label>
            <label>Mot de passe
                <input type="password" name="mot_de_passe" autocomplete="current-password" required>
            </label>
            <button type="submit">Se connecter</button>
        </form>

        <p class="lien">Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
    </section>
</main>
</body>
</html>