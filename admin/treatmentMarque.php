<?php
require_once '../assets/functions.php';
exigerAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValide($_POST['csrf'] ?? '')) {
    flash("Requête invalide, réessaie.", 'erreur');
    header('Location: marques.php');
    exit;
}

$action  = $_POST['action'] ?? '';
$id      = (int)($_POST['id'] ?? 0);
$dossier = __DIR__ . '/../images/marques';

/* ----- Suppression ----- */
if ($action === 'delete') {
    $nb = fetchOne($bdd, "SELECT COUNT(*) AS n FROM chaussures WHERE id_marque = ?", [$id])['n'];

    if ($nb > 0) {
        flash("Impossible : $nb chaussure(s) utilisent cette marque.", 'erreur');
    } else {
        $m = fetchOne($bdd, "SELECT logo FROM marques WHERE id = ?", [$id]);
        $bdd->prepare("DELETE FROM marques WHERE id = ?")->execute([$id]);

        if ($m && $m['logo'] && is_file($dossier . '/' . basename($m['logo']))) {
            unlink($dossier . '/' . basename($m['logo']));
        }
        flash("Marque supprimée.");
    }
    header('Location: marques.php');
    exit;
}

/* ----- Ajout / modification ----- */
$nom = trim($_POST['nom'] ?? '');
$erreur = '';

if ($nom === '' || mb_strlen($nom) > 100) {
    $erreur = "Le nom est obligatoire (100 caractères maximum).";
} else {
    $doublon = fetchOne($bdd, "SELECT id FROM marques WHERE nom = ? AND id != ?", [$nom, $id]);
    if ($doublon) {
        $erreur = "Cette marque existe déjà.";
    }
}

$logo = null;
if ($erreur === '') {
    $logo = uploadImage($_FILES['logo'] ?? ['error' => UPLOAD_ERR_NO_FILE], $dossier, $erreur);
}

if ($erreur !== '') {
    flash($erreur, 'erreur');
    header('Location: ' . ($action === 'update' ? "formMarque.php?id=$id" : 'formMarque.php'));
    exit;
}

if ($action === 'add') {
    $bdd->prepare("INSERT INTO marques (nom, logo) VALUES (?, ?)")->execute([$nom, $logo]);
    flash("Marque ajoutée.");
}

if ($action === 'update') {
    $ancien = fetchOne($bdd, "SELECT logo FROM marques WHERE id = ?", [$id]);

    if ($logo) {
        $bdd->prepare("UPDATE marques SET nom = ?, logo = ? WHERE id = ?")->execute([$nom, $logo, $id]);
        if ($ancien && $ancien['logo'] && is_file($dossier . '/' . basename($ancien['logo']))) {
            unlink($dossier . '/' . basename($ancien['logo']));
        }
    } else {
        $bdd->prepare("UPDATE marques SET nom = ? WHERE id = ?")->execute([$nom, $id]);
    }
    flash("Marque modifiée.");
}

header('Location: marques.php');
exit;
