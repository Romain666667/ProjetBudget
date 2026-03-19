<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function verifier_identifiants(string $username, string $password): bool {
    $stmt = db()->prepare(
        "SELECT mot_de_passe_hash FROM utilisateurs WHERE username = :username"
    );
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) return false;

    return password_verify($password, $row['mot_de_passe_hash']);
}
