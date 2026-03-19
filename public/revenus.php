<?php
declare(strict_types=1);
session_start(); // ✅ ajouté

require_once __DIR__ . '/../src/config.php';

if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/../src/depenses.php';
require_once __DIR__ . '/../src/revenus.php';

init_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_revenu') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        supprimer_revenu($id);
    }
    header('Location: ' . BASE_URL . '/revenus.php'); // ✅
    exit;
}

$erreur = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_revenu') {
    try {
        ajouter_revenu([
            'date'     => $_POST['date'] ?? '',
            'montant'  => $_POST['montant'] ?? '0',
            'note'     => $_POST['note'] ?? '',
            'personne' => $_POST['personne'] ?? '',
        ]);
        header('Location: ' . BASE_URL . '/revenus.php'); // ✅
        exit;
    } catch (Throwable $e) {
        $erreur = $e->getMessage();
    }
}

$revenus = lister_revenus();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes revenus</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css"> 
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Mes revenus</h1>
            <p class="muted">Ajoute tes entrées d'argent et retrouve-les ci-dessous.</p>
            <nav class="nav">
                <a class="btn" href="<?= BASE_URL ?>/index.php">Dépenses</a>  
                <a class="btn" href="<?= BASE_URL ?>/revenus.php">Revenus</a>
                <a class="btn" href="<?= BASE_URL ?>/budgets.php">Budgets</a>
                <a class="btn" href="<?= BASE_URL ?>/bilan.php">Bilan</a>
                <a class="btn" href="<?= BASE_URL ?>/logout.php">Déconnexion</a>
            </nav>
        </header>

        <section class="card">
            <h2>Nouveau revenu</h2>

            <?php if ($erreur): ?>
                <p class="error"><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>

            <form method="post" class="form">
                <input type="hidden" name="action" value="add_revenu">
                <label>
                    Date
                    <input type="date" name="date" required>
                </label>
                <label>
                    Montant (€)
                    <input type="number" name="montant" step="0.01" min="0" required>
                </label>
                <label>
                    Note (optionnel)
                    <input type="text" name="note" placeholder="ex: salaire, remboursement, etc.">
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
            <h2>Revenus</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="right">Montant</th>
                            <th>Note</th>
                            <th>Personne</th>
                            <th class="right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($revenus) === 0): ?>
                            <tr>
                                <td colspan="5" class="muted">Aucun revenu pour l'instant.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($revenus as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['date']) ?></td>
                                    <td class="right"><?= number_format((float)$r['montant'], 2, ',', ' ') ?> €</td>
                                    <td><?= htmlspecialchars((string)($r['note'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($r['personne'] ?? '')) ?></td>
                                    <td class="right">
                                        <form method="post" onsubmit="return confirm('Supprimer ce revenu ?');">
                                            <input type="hidden" name="action" value="delete_revenu">
                                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
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
    </div>
</body>
</html>
