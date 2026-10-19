<?php
require_once 'assets/functions.php';
exigerConnexion();

$retour = 'index.php?action=profil';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValide($_POST['csrf'] ?? '')) {
    flash("Requête invalide, réessaie.", 'erreur');
    header("Location: $retour");
    exit;
}

$id     = (int)$_SESSION['user']['id'];
$user   = fetchOne($bdd, "SELECT * FROM utilisateurs WHERE id = ?", [$id]);
$action = $_POST['action'] ?? '';

// Tout changement exige le mot de passe actuel
if (!$user || !password_verify($_POST['mdp_actuel'] ?? '', $user['mot_de_passe'])) {
    flash("Mot de passe actuel incorrect.", 'erreur');
    header('Location: ' . $retour . ($action === 'mdp' ? '&onglet=mdp' : ''));
    exit;
}

/* ----- Nom et email ----- */
if ($action === 'infos') {
    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nom === '' || mb_strlen($nom) > 100) {
        flash("Le nom est obligatoire (100 caractères maximum).", 'erreur');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash("L'email n'est pas valide.", 'erreur');
    } elseif (fetchOne($bdd, "SELECT id FROM utilisateurs WHERE email = ? AND id <> ?", [$email, $id])) {
        flash("Cet email est déjà utilisé.", 'erreur');
    } else {
        $bdd->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?")->execute([$nom, $email, $id]);
        $_SESSION['user']['nom'] = $nom;
        flash("Informations mises à jour.");
    }
    header("Location: $retour");
    exit;
}

/* ----- Mot de passe ----- */
if ($action === 'mdp') {
    $nouveau  = $_POST['nouveau_mdp'] ?? '';
    $nouveau2 = $_POST['nouveau_mdp2'] ?? '';

    if (strlen($nouveau) < 8) {
        flash("Le nouveau mot de passe doit faire au moins 8 caractères.", 'erreur');
    } elseif ($nouveau !== $nouveau2) {
        flash("Les deux nouveaux mots de passe ne correspondent pas.", 'erreur');
    } elseif ($nouveau === ($_POST['mdp_actuel'] ?? '')) {
        flash("Le nouveau mot de passe doit être différent de l'ancien.", 'erreur');
    } else {
        $bdd->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?")
            ->execute([hasherMdp($nouveau), $id]);
        session_regenerate_id(true);
        flash("Mot de passe modifié.");
        header("Location: $retour");
        exit;
    }
    header("Location: $retour&onglet=mdp");
    exit;
}

header("Location: $retour");
exit;
