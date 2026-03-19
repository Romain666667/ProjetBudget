<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function lister_budgets(): array {
    return db()->query(
        "SELECT b.*, c.nom AS categorie, c.couleur, c.icone
         FROM budgets b
         JOIN categories c ON c.id = b.categorie_id
         ORDER BY b.mois DESC, b.personne ASC, c.nom ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function ajouter_ou_modifier_budget(array $data): void {
    $stmt = db()->prepare(
        "INSERT INTO budgets (mois, categorie_id, personne, plafond)
         VALUES (:mois, :categorie_id, :personne, :plafond)
         ON DUPLICATE KEY UPDATE plafond = :plafond"
    );
    $stmt->execute([
        ':mois'         => $data['mois'],
        ':categorie_id' => (int)$data['categorie_id'],
        ':personne'     => $data['personne'],
        ':plafond'      => (float)$data['plafond'],
    ]);
}

function supprimer_budget(int $id): void {
    $stmt = db()->prepare("DELETE FROM budgets WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

function budgets_vs_reel(string $mois): array {
    $stmt = db()->prepare(
        "SELECT b.id, c.nom AS categorie, c.couleur, c.icone,
                b.personne, b.plafond,
                COALESCE(SUM(d.montant), 0) AS depense_reelle
         FROM budgets b
         JOIN categories c ON c.id = b.categorie_id
         LEFT JOIN depenses d
           ON d.categorie_id = b.categorie_id
          AND DATE_FORMAT(d.date, '%Y-%m') = b.mois
          AND d.personne = b.personne
         WHERE b.mois = :mois
         GROUP BY b.id, c.nom, c.couleur, c.icone, b.personne, b.plafond"
    );
    $stmt->execute([':mois' => $mois]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
