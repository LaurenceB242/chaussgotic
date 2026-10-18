<?php
try {
    $bdd = new PDO(
        "mysql:host=localhost;dbname=chaussGotic;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Impossible de se connecter à la base de données.");
}