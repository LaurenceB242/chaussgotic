<?php
require_once 'assets/functions.php';
exigerConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValide($_POST['csrf'] ?? '')) {
    flash("Requête invalide, réessaie.", 'erreur');
    header('Location: index.php?action=panier');
    exit;
}

$lignes = panierLignes($bdd);
if (empty($lignes)) {
    flash("Ton panier est vide.", 'erreur');
    header('Location: index.php?action=panier');
    exit;
}

try {
    $bdd->beginTransaction();
    $total = 0;

    // 1. Vérification du stock, lignes verrouillées
    foreach ($lignes as $l) {
        $s = fetchOne($bdd,
            "SELECT quantite FROM stock WHERE id_chaussure = ? AND id_pointure = ? FOR UPDATE",
            [$l['id'], $l['id_pointure']]);

        if (!$s || $s['quantite'] < $l['quantite']) {
            throw new RuntimeException(
                "Stock insuffisant pour « {$l['nom']} » en pointure " . (float)$l['pointure'] . ".");
        }
        $total += (float)$l['prix'] * $l['quantite'];
    }

    // 2. Création de la commande
    $bdd->prepare("INSERT INTO commandes (id_utilisateur, total) VALUES (?, ?)")
        ->execute([$_SESSION['user']['id'], round($total, 2)]);
    $idCommande = (int)$bdd->lastInsertId();

    // 3. Lignes et décrément du stock
    $stmtLigne = $bdd->prepare(
        "INSERT INTO commande_lignes (id_commande, id_chaussure, nom, pointure, prix_unitaire, quantite)
         VALUES (?, ?, ?, ?, ?, ?)");
    $stmtStock = $bdd->prepare(
        "UPDATE stock SET quantite = quantite - ? WHERE id_chaussure = ? AND id_pointure = ?");

    foreach ($lignes as $l) {
        $stmtLigne->execute([$idCommande, $l['id'], $l['nom'], $l['pointure'], $l['prix'], $l['quantite']]);
        $stmtStock->execute([$l['quantite'], $l['id'], $l['id_pointure']]);
    }

    $bdd->commit();
    $_SESSION['panier'] = [];
    flash("Commande n°$idCommande enregistrée. Merci !");
    header('Location: index.php?action=commandes');
    exit;

} catch (RuntimeException $e) {
    $bdd->rollBack();
    flash($e->getMessage(), 'erreur');
    header('Location: index.php?action=panier');
    exit;
} catch (Throwable $e) {
    if ($bdd->inTransaction()) {
        $bdd->rollBack();
    }
    flash("Erreur pendant la commande, réessaie.", 'erreur');
    header('Location: index.php?action=panier');
    exit;
}