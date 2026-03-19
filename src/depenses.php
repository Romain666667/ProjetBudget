<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function init_db(): void {}

function ajouter_depense(array $data): void {
    $personne = trim($data['personne'] ?? '');
    if ($personne === '') throw new Exception("Personne obligatoire");

    $stmt = db()->prepare(
        "INSERT INTO depenses(date, categorie_id, montant, note, personne)
         VALUES(:date, :categorie_id, :montant, :note, :personne)"
    );
    $stmt->execute([
        ':date'         => $data['date'],
        ':categorie_id' => (int)$data['categorie_id'],
        ':montant'      => (float)$data['montant'],
        ':note'         => ($data['note'] ?? '') !== '' ? $data['note'] : null,
        ':personne'     => $personne,
    ]);
}

function lister_depenses(): array {
    return db()->query(
        "SELECT d.*, c.nom AS categorie, c.couleur, c.icone
         FROM depenses d
         JOIN categories c ON c.id = d.categorie_id
         ORDER BY d.date DESC, d.id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function totaux_par_mois_et_categorie_et_personne(): array {
    return db()->query(
        "SELECT DATE_FORMAT(d.date, '%Y-%m') AS mois,
                c.nom AS categorie,
                c.couleur,
                c.icone,
                d.personne,
                SUM(d.montant) AS total
         FROM depenses d
         JOIN categories c ON c.id = d.categorie_id
         GROUP BY mois, c.nom, c.couleur, c.icone, d.personne
         ORDER BY mois DESC, c.nom ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function supprimer_depense(int $id): void {
    $stmt = db()->prepare("DELETE FROM depenses WHERE id = :id");
    $stmt->execute([':id' => $id]);
}
