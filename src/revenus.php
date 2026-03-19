<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function ajouter_revenu(array $data): void {
  $personne = trim($data['personne'] ?? '');
  if ($personne === '') throw new Exception("Personne obligatoire");

  $stmt = db()->prepare(
    "INSERT INTO revenus(date, montant, note, personne)
     VALUES(:date, :montant, :note, :personne)"
  );
  $stmt->execute([
    ':date' => $data['date'],
    ':montant' => (float)$data['montant'],
    ':note' => ($data['note'] ?? '') !== '' ? $data['note'] : null,
    ':personne' => $personne,
  ]);
}

function lister_revenus(): array {
  return db()->query("SELECT * FROM revenus ORDER BY date DESC, id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
}

function supprimer_revenu(int $id): void {
  $stmt = db()->prepare("DELETE FROM revenus WHERE id = :id");
  $stmt->execute([':id' => $id]);
}