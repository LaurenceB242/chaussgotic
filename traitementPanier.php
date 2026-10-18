<?php
require_once 'assets/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValide($_POST['csrf'] ?? '')) {
    flash("Requête invalide, réessaie.", 'erreur');
    header('Location: index.php?action=panier');
    exit;
}

$action = $_POST['action'] ?? '';
$_SESSION['panier'] = $_SESSION['panier'] ?? [];

/* ----- Ajout ----- */
if ($action === 'add') {
    $idC = (int)($_POST['id_chaussure'] ?? 0);
    $idP = (int)($_POST['id_pointure'] ?? 0);
    $qte = max(1, min(10, (int)($_POST['quantite'] ?? 1)));

    $s = fetchOne($bdd,
        "SELECT quantite FROM stock WHERE id_chaussure = ? AND id_pointure = ?", [$idC, $idP]);

    if (!$s || $s['quantite'] < 1) {
        flash("Cette pointure n'est pas disponible.", 'erreur');
        header("Location: index.php?action=chaussure&id=$idC");
        exit;
    }

    $cle = "$idC:$idP";
    $nouvelle = ($_SESSION['panier'][$cle] ?? 0) + $qte;

    if ($nouvelle > $s['quantite']) {
        $nouvelle = (int)$s['quantite'];
        flash("Quantité limitée au stock disponible ($nouvelle).", 'erreur');
    } else {
        flash("Ajouté au panier.");
    }
    $_SESSION['panier'][$cle] = $nouvelle;

    header('Location: index.php?action=panier');
    exit;
}

/* ----- Modification de quantité ----- */
if ($action === 'update') {
    $cle = $_POST['cle'] ?? '';
    $qte = (int)($_POST['quantite'] ?? 0);

    if (isset($_SESSION['panier'][$cle])) {
        [$idC, $idP] = array_map('intval', explode(':', $cle));
        $s = fetchOne($bdd,
            "SELECT quantite FROM stock WHERE id_chaussure = ? AND id_pointure = ?", [$idC, $idP]);

        if ($qte <= 0 || !$s) {
            unset($_SESSION['panier'][$cle]);
        } else {
            $_SESSION['panier'][$cle] = min($qte, (int)$s['quantite']);
        }
    }
    header('Location: index.php?action=panier');
    exit;
}

/* ----- Suppression d'une ligne / vider ----- */
if ($action === 'remove') {
    unset($_SESSION['panier'][$_POST['cle'] ?? '']);
}
if ($action === 'clear') {
    $_SESSION['panier'] = [];
}

header('Location: index.php?action=panier');
exit;