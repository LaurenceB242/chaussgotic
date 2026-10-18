<?php
require_once 'assets/functions.php';

$erreurs = [];
$nom = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';
    $mdp2  = $_POST['mot_de_passe2'] ?? '';

    if ($nom === '') {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide.";
    }
    if (strlen($mdp) < 8) {
        $erreurs[] = "Le mot de passe doit faire au moins 8 caractères.";
    }
    if ($mdp !== $mdp2) {
        $erreurs[] = "Les deux mots de passe ne correspondent pas.";
    }

    if (empty($erreurs)) {
        $existe = fetchOne($bdd, "SELECT id FROM utilisateurs WHERE email = ?", [$email]);

        if ($existe) {
            $erreurs[] = "Cet email est déjà utilisé.";
        } else {
           $hash = hasherMdp($mdp);
            $stmt = $bdd->prepare(
                "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)"
            );
            $stmt->execute([$nom, $email, $hash]);

            header('Location: connexion.php?inscrit=1');
            exit;
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
</head>
<body>
    <h1>Créer un compte</h1>

    <?php foreach ($erreurs as $erreur): ?>
        <p class="erreur"><?= e($erreur) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <label>Nom
            <input type="text" name="nom" value="<?= e($nom) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required>
        </label>
        <label>Mot de passe
            <input type="password" name="mot_de_passe" minlength="8" required>
        </label>
        <label>Confirmer le mot de passe
            <input type="password" name="mot_de_passe2" minlength="8" required>
        </label>
        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
</body>
</html>