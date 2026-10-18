<?php
require_once 'assets/functions.php';

if (estConnecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    $user = fetchOne($bdd, "SELECT * FROM utilisateurs WHERE email = ?", [$email]);

    if ($user && password_verify($mdp, $user['mot_de_passe'])) {

        if (mdpAReHasher($user['mot_de_passe'])) {
            $nouveau = hasherMdp($mdp);
            $bdd->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?")
                ->execute([$nouveau, $user['id']]);
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>

    <?php if (isset($_GET['inscrit'])): ?>
        <p class="succes">Compte créé, tu peux te connecter.</p>
    <?php endif; ?>

    <?php if ($erreur): ?>
        <p class="erreur"><?= e($erreur) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Email
            <input type="email" name="email" required>
        </label>
        <label>Mot de passe
            <input type="password" name="mot_de_passe" required>
        </label>
        <button type="submit">Se connecter</button>
    </form>

    <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
</body>
</html>