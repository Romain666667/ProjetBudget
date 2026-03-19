<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../src/config.php';

if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header('Location: ' . BASE_URL . '/login.php'); // ✅ corrigé
    exit;
}

require_once __DIR__ . '/../src/depenses.php'; // pour init_db()
require_once __DIR__ . '/../src/bilan.php';

init_db();

$depenses = total_depense_par_mois_et_personne();
$revenus  = total_revenu_par_mois_et_personne();

/*
  On construit une structure :
  $data['2026-02']['Romain'] = ['revenu'=>..., 'depense'=>...]
*/
$data = [];

// Dépenses
foreach ($depenses as $d) {
  $mois = (string)$d['mois'];
  $personne = (string)$d['personne'];
  $data[$mois][$personne]['depense'] = (float)$d['total'];
}

// Revenus
foreach ($revenus as $r) {
  $mois = (string)$r['mois'];
  $personne = (string)$r['personne'];
  $data[$mois][$personne]['revenu'] = (float)$r['total'];
}

// Complète les champs manquants + calcule "reste"
foreach ($data as $mois => $personnes) {
  foreach ($personnes as $personne => $vals) {
    $rev = (float)($vals['revenu'] ?? 0);
    $dep = (float)($vals['depense'] ?? 0);
    $data[$mois][$personne] = [
      'revenu' => $rev,
      'depense' => $dep,
      'reste' => $rev - $dep,
    ];
  }
}

// Tri des mois décroissant (au cas où)
krsort($data);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bilan</title>
  <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <h1>Bilan</h1>
      <p class="muted">Revenus - Dépenses = Reste.</p>

      <nav class="nav">
        <a class="btn" href="/Projet/public/index.php">Dépenses</a>
        <a class="btn" href="/Projet/public/revenus.php">Revenus</a>
        <a class="btn" href="<?= BASE_URL ?>/budgets.php">Budgets</a>
        <a class="btn" href="/Projet/public/bilan.php">Bilan</a>
        <a class="btn" href="/Projet/public/logout.php">Déconnexion</a>
      </nav>
    </header>

    <section class="card">
      <h2>Bilan par mois (par personne)</h2>

      <?php if (count($data) === 0): ?>
        <p class="muted">Pas encore de données.</p>
      <?php else: ?>
        <?php foreach ($data as $mois => $personnes): ?>
          <h3 class="mois-title"><?= htmlspecialchars($mois) ?></h3>

          <div class="table-wrap">
            <table class="table table-totaux">
              <thead>
                <tr>
                  <th>Personne</th>
                  <th class="right">Revenus</th>
                  <th class="right">Dépenses</th>
                  <th class="right">Reste</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($personnes as $personne => $vals): ?>
                  <tr>
                    <td><?= htmlspecialchars($personne) ?></td>
                    <td class="right"><?= number_format((float)$vals['revenu'], 2, ',', ' ') ?> €</td>
                    <td class="right"><?= number_format((float)$vals['depense'], 2, ',', ' ') ?> €</td>
                    <td class="right"><?= number_format((float)$vals['reste'], 2, ',', ' ') ?> €</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</body>
</html>
