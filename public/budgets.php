<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../src/config.php';

if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/../src/budgets.php';
require_once __DIR__ . '/../src/categories.php';

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_budget') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) supprimer_budget($id);
    header('Location: ' . BASE_URL . '/budgets.php');
    exit;
}

// Ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_budget') {
    ajouter_ou_modifier_budget([
        'mois'      => $_POST['mois']      ?? '',
        'categorie' => $_POST['categorie'] ?? '',
        'personne'  => $_POST['personne']  ?? '',
        'plafond'   => $_POST['plafond']   ?? '0',
    ]);
    header('Location: ' . BASE_URL . '/budgets.php');
    exit;
}

$mois_filtre = $_GET['mois'] ?? date('Y-m');
$budgets     = lister_budgets();
$categories  = lister_categories();
$bilan       = budgets_vs_reel($mois_filtre);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Budgets</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <header class="header">
        <h1>Budgets</h1>
        <p class="muted">Définis des plafonds par catégorie et par personne.</p>
        <nav class="nav">
            <a class="btn" href="<?= BASE_URL ?>/index.php">Dépenses</a>
            <a class="btn" href="<?= BASE_URL ?>/revenus.php">Revenus</a>
            <a class="btn" href="<?= BASE_URL ?>/budgets.php">Budgets</a>
            <a class="btn" href="<?= BASE_URL ?>/bilan.php">Bilan</a>
            <a class="btn" href="<?= BASE_URL ?>/logout.php">Déconnexion</a>
        </nav>
    </header>

    <!-- Formulaire ajout budget -->
    <section class="card">
        <h2>Définir un budget</h2>
        <form method="post" class="form">
            <input type="hidden" name="action" value="add_budget">
            <label>
                Mois
                <input type="month" name="mois" value="<?= date('Y-m') ?>" required>
            </label>
            <label>
                Catégorie
                <select name="categorie" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['nom']) ?>">
                            <?= htmlspecialchars($cat['icone'] . ' ' . $cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Personne
                <select name="personne" required>
                    <option value="Romain">Romain</option>
                    <option value="Juliette">Juliette</option>
                </select>
            </label>
            <label>
                Plafond (€)
                <input type="number" name="plafond" step="0.01" min="0" required>
            </label>
            <button class="btn" type="submit">Enregistrer</button>
        </form>
    </section>

    <!-- Suivi budgets vs réel -->
    <section class="card">
        <h2>Suivi du mois</h2>
        <form method="get" style="margin-bottom:1rem;">
            <label>
                Mois affiché
                <input type="month" name="mois" value="<?= htmlspecialchars($mois_filtre) ?>"
                       onchange="this.form.submit()">
            </label>
        </form>

        <?php if (count($bilan) === 0): ?>
            <p class="muted">Aucun budget défini pour ce mois.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Personne</th>
                            <th>Catégorie</th>
                            <th class="right">Plafond</th>
                            <th class="right">Dépensé</th>
                            <th class="right">Restant</th>
                            <th>Progression</th>
                            <th class="right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bilan as $b):
                            $pct      = $b['plafond'] > 0
                                        ? min(100, round($b['depense_reelle'] / $b['plafond'] * 100))
                                        : 100;
                            $restant  = $b['plafond'] - $b['depense_reelle'];
                            $couleur  = $pct >= 100 ? '#dc3545' : ($pct >= 75 ? '#fd7e14' : '#28a745');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($b['personne']) ?></td>
                            <td><?= htmlspecialchars($b['categorie']) ?></td>
                            <td class="right"><?= number_format((float)$b['plafond'], 2, ',', ' ') ?> €</td>
                            <td class="right"><?= number_format((float)$b['depense_reelle'], 2, ',', ' ') ?> €</td>
                            <td class="right" style="color:<?= $restant < 0 ? '#dc3545' : 'inherit' ?>">
                                <?= number_format($restant, 2, ',', ' ') ?> €
                            </td>
                            <td style="min-width:120px;">
                                <div style="background:#e9ecef;border-radius:4px;height:10px;">
                                    <div style="width:<?= $pct ?>%;background:<?= $couleur ?>;height:10px;border-radius:4px;"></div>
                                </div>
                                <small><?= $pct ?>%</small>
                            </td>
                            <td class="right">
                                <form method="post" onsubmit="return confirm('Supprimer ce budget ?');">
                                    <input type="hidden" name="action" value="delete_budget">
                                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                    <button class="btn btn-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
