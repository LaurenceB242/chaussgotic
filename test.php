<?php
require_once 'assets/functions.php';
$r = fetchOne($bdd, "SELECT COUNT(*) AS n FROM utilisateurs");
echo "Connexion OK, utilisateurs : " . $r['n'];