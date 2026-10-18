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