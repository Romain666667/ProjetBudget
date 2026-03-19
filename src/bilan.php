<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function total_depense_par_mois_et_personne(): array {
    return db()->query(
        "SELECT DATE_FORMAT(date, '%Y-%m') AS mois,
                personne,
                SUM(montant) AS total
         FROM depenses
         GROUP BY mois, personne
         ORDER BY mois DESC, personne ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function total_revenu_par_mois_et_personne(): array {
    return db()->query(
        "SELECT DATE_FORMAT(date, '%Y-%m') AS mois,
                personne,
                SUM(montant) AS total
         FROM revenus
         GROUP BY mois, personne
         ORDER BY mois DESC, personne ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}
