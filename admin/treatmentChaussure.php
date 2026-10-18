<?php
require_once '../assets/functions.php';
exigerAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValide($_POST['csrf'] ?? '')) {
    flash("Requête invalide, réessaie.", 'erreur');
    header('Location: chaussures.php');
    exit;
}

$action  = $_POST['action'] ?? '';
$id      = (int)($_POST['id'] ?? 0);
$dossier = __DIR__ . '/../images/chaussures';

/* ----- Suppression ----- */
if ($action === 'delete') {
    $imgs = fetchAll($bdd, "SELECT path FROM images WHERE id_chaussure = ?", [$id]);

    // ON DELETE CASCADE supprime aussi les lignes images et stock
    $bdd->prepare("DELETE FROM chaussures WHERE id = ?")->execute([$id]);

    foreach ($imgs as $i) {
        supprimerFichier($dossier, $i['path']);
    }
    flash("Chaussure supprimée.");
    header('Location: chaussures.php');
    exit;
}

if (!in_array($action, ['add', 'update'], true)) {
    header('Location: chaussures.php');
    exit;
}

/* ----- Validation ----- */
$nom         = trim($_POST['nom'] ?? '');
$description = trim($_POST['description'] ?? '');
$prix        = str_replace(',', '.', trim($_POST['prix'] ?? ''));
$idMarque    = (int)($_POST['id_marque'] ?? 0);
$erreur      = '';

if ($nom === '' || mb_strlen($nom) > 150) {
    $erreur = "Le nom est obligatoire (150 caractères maximum).";
} elseif (!is_numeric($prix) || $prix < 0 || $prix > 99999.99) {
    $erreur = "Le prix n'est pas valide.";
} elseif (!fetchOne($bdd, "SELECT id FROM marques WHERE id = ?", [$idMarque])) {
    $erreur = "Marque invalide.";
} elseif ($action === 'update' && !fetchOne($bdd, "SELECT id FROM chaussures WHERE id = ?", [$id])) {
    $erreur = "Chaussure introuvable.";
}

/* ----- Upload des nouvelles images ----- */
$nouvelles = [];
if ($erreur === '') {
    $fichiers = array_filter(
        fichiersMultiples($_FILES['images'] ?? []),
        fn($f) => $f['error'] !== UPLOAD_ERR_NO_FILE
    );

    if (count($fichiers) > 8) {
        $erreur = "8 images maximum par envoi.";
    } else {
        foreach ($fichiers as $f) {
            $fichier = uploadImage($f, $dossier, $erreur);
            if ($erreur !== '') {
                break;
            }
            if ($fichier) {
                $nouvelles[] = $fichier;
            }
        }
    }
}

// En cas d'erreur : on retire les images déjà envoyées et on revient au formulaire
if ($erreur !== '') {
    foreach ($nouvelles as $n) {
        supprimerFichier($dossier, $n);
    }
    $_SESSION['old'] = [
        'nom' => $nom, 'description' => $description,
        'prix' => $prix, 'id_marque' => $idMarque,
    ];
    flash($erreur, 'erreur');
    header('Location: ' . ($action === 'update' ? "formChaussure.php?id=$id" : 'formChaussure.php'));
    exit;
}

/* ----- Enregistrement en transaction ----- */
$fichiersASupprimer = [];

try {
    $bdd->beginTransaction();

    if ($action === 'add') {
        $bdd->prepare("INSERT INTO chaussures (nom, description, prix, id_marque) VALUES (?, ?, ?, ?)")
            ->execute([$nom, $description, $prix, $idMarque]);
        $id = (int)$bdd->lastInsertId();
    } else {
        $bdd->prepare("UPDATE chaussures SET nom = ?, description = ?, prix = ?, id_marque = ? WHERE id = ?")
            ->execute([$nom, $description, $prix, $idMarque, $id]);
    }

    // Images cochées pour suppression (on vérifie qu'elles appartiennent bien à cette chaussure)
    foreach (array_map('intval', (array)($_POST['suppr_images'] ?? [])) as $imgId) {
        $img = fetchOne($bdd, "SELECT path FROM images WHERE id = ? AND id_chaussure = ?", [$imgId, $id]);
        if ($img) {
            $bdd->prepare("DELETE FROM images WHERE id = ?")->execute([$imgId]);
            $fichiersASupprimer[] = $img['path'];
        }
    }

    // Nouvelles images
    $stmtImg = $bdd->prepare("INSERT INTO images (id_chaussure, path) VALUES (?, ?)");
    foreach ($nouvelles as $n) {
        $stmtImg->execute([$id, $n]);
    }

    // Stock : une ligne par pointure, créée ou mise à jour
    $pointuresValides = array_map('intval', array_column(fetchAll($bdd, "SELECT id FROM pointures"), 'id'));
    $stmtStock = $bdd->prepare(
        "INSERT INTO stock (id_chaussure, id_pointure, quantite) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE quantite = VALUES(quantite)"
    );
    foreach ((array)($_POST['stock'] ?? []) as $idPointure => $quantite) {
        $idPointure = (int)$idPointure;
        if (!in_array($idPointure, $pointuresValides, true)) {
            continue;
        }
        $stmtStock->execute([$id, $idPointure, max(0, min(9999, (int)$quantite))]);
    }

    $bdd->commit();

} catch (Throwable $e) {
    if ($bdd->inTransaction()) {
        $bdd->rollBack();
    }
    foreach ($nouvelles as $n) {
        supprimerFichier($dossier, $n);
    }
    flash("Erreur lors de l'enregistrement.", 'erreur');
    header('Location: chaussures.php');
    exit;
}

// Les fichiers ne sont effacés qu'une fois la transaction validée
foreach ($fichiersASupprimer as $f) {
    supprimerFichier($dossier, $f);
}

flash($action === 'add' ? "Chaussure ajoutée." : "Chaussure modifiée.");
header('Location: chaussures.php');
exit;