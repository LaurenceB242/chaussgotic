<?php
require_once 'assets/functions.php';

function check($label, $ok, $detail = '') {
    echo ($ok ? '✅ ' : '❌ ') . $label . ($detail ? " : $detail" : '') . "<br>";
}

echo "<h2>Diagnostic</h2>";

// Fonctions
foreach (['fetchAll','fetchOne','e','estConnecte','estAdmin','exigerAdmin',
          'hasherMdp','mdpAReHasher','algoMdp','optionsMdp'] as $f) {
    check("Fonction $f()", function_exists($f));
}

// Argon2id
check('Argon2id disponible', defined('PASSWORD_ARGON2ID'),
      defined('PASSWORD_ARGON2ID') ? '' : 'bcrypt sera utilisé à la place');

// Hachage
$h = hasherMdp('MotDePasseTest123!');
check('Hachage', strlen($h) > 50, substr($h, 0, 12) . '...');
check('Vérification correcte', password_verify('MotDePasseTest123!', $h));
check('Mauvais mot de passe refusé', !password_verify('autre', $h));
check('Pas de re-hachage inutile', !mdpAReHasher($h));

// Base
try {
    $n = fetchOne($bdd, "SELECT COUNT(*) AS n FROM utilisateurs")['n'];
    check('Table utilisateurs', true, "$n utilisateur(s)");
    $admins = fetchOne($bdd, "SELECT COUNT(*) AS n FROM utilisateurs WHERE role='admin'")['n'];
    check('Un admin existe', $admins > 0, $admins > 0 ? '' : 'lance creer_admin.php');
} catch (Exception $e) {
    check('Table utilisateurs', false, $e->getMessage());
}

// Fichiers
foreach (['inscription.php','connexion.php','deconnexion.php','403.php','index.php','admin/dashboard.php'] as $f) {
    check("Fichier $f", file_exists(__DIR__ . '/' . $f));
}