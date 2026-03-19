<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/config.php';


if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header('Location: ' . BASE_URL . '/login.php'); // ✅ corrigé
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../src/depenses.php';
require_once __DIR__ . '/../src/categories.php';

init_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        supprimer_depense($id);
    }
    header('Location: ' . BASE_URL . '/index.php'); // ✅ corrigé
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    ajouter_depense([
        'date'         => $_POST['date']         ?? '',
        'categorie_id' => $_POST['categorie_id'] ?? '0', // ← modifié
        'montant'      => $_POST['montant']      ?? '0',
        'note'         => $_POST['note']         ?? '',
        'personne'     => $_POST['personne']     ?? '',
    ]);
    header('Location: ' . BASE_URL . '/index.php'); // ✅ corrigé
    exit;
}

$depenses = lister_depenses();
$totauxMoisCategorie = totaux_par_mois_et_categorie_et_personne();

$grouped = [];
foreach ($totauxMoisCategorie as $row) {
    $mois     = $row['mois'];
    $personne = $row['personne'];
    $grouped[$mois][$personne][] = $row;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon budget</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Mon budget</h1>
            <p class="muted">Ajoute tes dépenses et retrouve-les ci-dessous.</p>
            <nav class="nav">
                <a class="btn" href="<?= BASE_URL ?>/index.php">Dépenses</a>
                <a class="btn" href="<?= BASE_URL ?>/revenus.php">Revenus</a>
                <a class="btn" href="<?= BASE_URL ?>/budgets.php">Budgets</a>
                <a class="btn" href="<?= BASE_URL ?>/bilan.php">Bilan</a>
                <a class="btn" href="<?= BASE_URL ?>/logout.php">Déconnexion</a>
            </nav>
        </header>

        <section class="card">
            <h2>Nouvelle dépense</h2>
            <form method="post" class="form">
                <input type="hidden" name="action" value="add">
                <label>
                    Date
                    <input type="date" name="date" required>
                </label>
                <?php $categories = lister_categories(); ?>
                <label>
                    Catégorie
                    <select name="categorie_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['icone'] . ' ' . $cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Montant (€)
                    <input type="number" name="montant" step="0.01" min="0" required>
                </label>
                <label>
                    Note (optionnel)
                    <input type="text" name="note" placeholder="ex: McDo, soirée, etc.">
                </label>
                <label>
                    Personne
                    <select name="personne" required>
                        <option value="Romain">Romain</option>
                        <option value="Juliette">Juliette</option>
                    </select>
                </label>
                <button class="btn" type="submit">Ajouter</button>
            </form>
        </section>

        <section class="card">
            <h2>Dépenses</h2>
            <div class="table-wrap <?= count($depenses) > 10 ? 'table-wrap-scroll' : '' ?>">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Catégorie</th>
                            <th class="right">Montant</th>
                            <th>Note</th>
                            <th>Personne</th>
                            <th class="right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($depenses) === 0): ?>
                            <tr>
                                <td colspan="6" class="muted">Aucune dépense pour l'instant.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($depenses as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['date']) ?></td>
                                    <td><?= htmlspecialchars($d['categorie']) ?></td>
                                    <td class="right"><?= number_format((float)$d['montant'], 2, ',', ' ') ?> €</td>
                                    <td><?= htmlspecialchars((string)($d['note'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($d['personne'] ?? '')) ?></td>
                                    <td class="right">
                                        <form method="post" onsubmit="return confirm('Supprimer cette dépense ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <h2>Totaux par mois (par catégorie et par personne)</h2>
            <?php if (count($grouped) === 0): ?>
                <p class="muted">Aucune dépense pour l'instant.</p>
            <?php else: ?>
                <?php foreach ($grouped as $mois => $personnes): ?>
                    <h3 class="mois-title"><?= htmlspecialchars($mois) ?></h3>
                    <?php foreach ($personnes as $personne => $rows): ?>
                        <h4 class="person-title"><?= htmlspecialchars($personne) ?></h4>
                        <div class="table-wrap">
                            <table class="table table-totaux">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th class="right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['categorie']) ?></td>
                                            <td class="right"><?= number_format((float)$r['total'], 2, ',', ' ') ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
