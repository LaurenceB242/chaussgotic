<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/bdd.php';

/* ---------- Base de données ---------- */

function fetchAll(PDO $bdd, string $sql, array $params = []): array {
    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchOne(PDO $bdd, string $sql, array $params = []) {
    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/* ---------- Affichage sécurisé ---------- */

function e($valeur) {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

/* ---------- Rôles ---------- */

function estConnecte() {
    return isset($_SESSION['user']);
}

function estAdmin() {
    return estConnecte() && $_SESSION['user']['role'] === 'admin';
}

function exigerConnexion() {
    if (!estConnecte()) {
        header('Location: /connexion.php');
        exit;
    }
}

function exigerAdmin() {
    if (!estAdmin()) {
        http_response_code(403);
        require __DIR__ . '/../403.php';
        exit;
    }
}

/* ---------- Mots de passe ---------- */

function optionsMdp() {
    return [
        'memory_cost' => 65536, // 64 Mo
        'time_cost'   => 4,     // 4 itérations
        'threads'     => 1,
    ];
}

function algoMdp() {
    // Argon2id si PHP le supporte, sinon bcrypt en secours
    return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
}

function hasherMdp($mdp) {
    $algo = algoMdp();
    $options = ($algo === PASSWORD_BCRYPT) ? ['cost' => 13] : optionsMdp();
    return password_hash($mdp, $algo, $options);
}

function mdpAReHasher($hash) {
    $algo = algoMdp();
    $options = ($algo === PASSWORD_BCRYPT) ? ['cost' => 13] : optionsMdp();
    return password_needs_rehash($hash, $algo, $options);
}

/* ---------- CSRF ---------- */

function csrfToken() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfValide($token) {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token);
}

/* ---------- Messages flash ---------- */

function flash($message, $type = 'succes') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function lireFlash() {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Upload d'image ---------- */

function uploadImage(array $file, string $dossier, &$erreur) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // pas d'image envoyée, ce n'est pas une erreur
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $erreur = "Erreur pendant l'envoi de l'image.";
        return null;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $erreur = "Image trop lourde (2 Mo maximum).";
        return null;
    }

    // On vérifie le vrai type du fichier, pas seulement son extension
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $autorises = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($autorises[$mime])) {
        $erreur = "Format non accepté (JPG, PNG ou WebP).";
        return null;
    }

    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }

    // Nom aléatoire : jamais le nom envoyé par l'utilisateur
    $nom = bin2hex(random_bytes(12)) . '.' . $autorises[$mime];

    if (!move_uploaded_file($file['tmp_name'], $dossier . '/' . $nom)) {
        $erreur = "Impossible d'enregistrer l'image.";
        return null;
    }
    return $nom;
}