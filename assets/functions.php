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