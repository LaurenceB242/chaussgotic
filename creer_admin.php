<?php
require_once 'assets/functions.php';

$hash = hasherMdp('ChoisisUnMotDePasseLongEtUnique!');
$bdd->prepare(
    "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'admin')"
)->execute(['Admin', 'admin@chaussgotic.local', $hash]);

echo 'Admin créé.';