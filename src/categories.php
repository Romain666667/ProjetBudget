<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function lister_categories(): array {
    return db()->query("SELECT * FROM categories ORDER BY nom ASC")
               ->fetchAll(PDO::FETCH_ASSOC);
}

function ajouter_categorie(array $data): void {
    $stmt = db()->prepare(
        "INSERT INTO categories (nom, couleur, icone)
         VALUES (:nom, :couleur, :icone)"
    );
    $stmt->execute([
        ':nom'     => trim($data['nom']),
        ':couleur' => $data['couleur'] ?? '#6c757d',
        ':icone'   => $data['icone']   ?? '📦',
    ]);
}

function supprimer_categorie(int $id): void {
    $stmt = db()->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
}
