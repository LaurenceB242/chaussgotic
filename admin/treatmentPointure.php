<?php
$titre = 'Chaussures'; require 'partials/header.php';
require_once '../assets/functions.php';
exigerAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValide($_POST['csrf'] ?? '')) {
    flash("Requête invalide, réessaie.", 'erreur');
    header('Location: pointures.php');
    exit;
}

$action = $_POST['action'] ?? '';

/* ----- Ajout d'une pointure ----- */
if ($action === 'add') {
    $valeur = str_replace(',', '.', trim($_POST['valeur'] ?? ''));

    if (!is_numeric($valeur) || $valeur < 15 || $valeur > 60) {
        flash("Pointure invalide (entre 15 et 60).", 'erreur');
    } else {
        $valeur = round((float)$valeur * 2) / 2;   // arrondi au demi le plus proche
        $existe = fetchOne($bdd, "SELECT id FROM pointures WHERE valeur = ?", [$valeur]);

        if ($existe) {
            flash("Cette pointure existe déjà.", 'erreur');
        } else {
            $bdd->prepare("INSERT INTO pointures (valeur) VALUES (?)")->execute([$valeur]);
            flash("Pointure ajoutée.");
        }
    }
    header('Location: pointures.php');
    exit;
}

/* ----- Ajout d'une série ----- */
if ($action === 'add_serie') {
    $debut = (int)($_POST['debut'] ?? 0);
    $fin   = (int)($_POST['fin'] ?? 0);
    $demi  = isset($_POST['demi']);

    if ($debut < 15 || $fin > 60 || $debut > $fin) {
        flash("Série invalide (de 15 à 60, début avant fin).", 'erreur');
    } else {
        $stmt = $bdd->prepare("INSERT IGNORE INTO pointures (valeur) VALUES (?)");
        $ajoutees = 0;

        for ($v = $debut; $v <= $fin; $v++) {
            $stmt->execute([$v]);
            $ajoutees += $stmt->rowCount();

            if ($demi && $v < $fin) {
                $stmt->execute([$v + 0.5]);
                $ajoutees += $stmt->rowCount();
            }
        }
        flash("$ajoutees pointure(s) ajoutée(s).");
    }
    header('Location: pointures.php');
    exit;
}

/* ----- Suppression ----- */
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $nb = fetchOne($bdd, "SELECT COUNT(*) AS n FROM stock WHERE id_pointure = ?", [$id])['n'];

    if ($nb > 0) {
        flash("Impossible : $nb chaussure(s) ont du stock lié à cette pointure.", 'erreur');
    } else {
        $bdd->prepare("DELETE FROM pointures WHERE id = ?")->execute([$id]);
        flash("Pointure supprimée.");
    }
    header('Location: pointures.php');
    exit;
}

header('Location: pointures.php');
exit;