<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php'; // ← nouveau

$error = null;

if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if (verifier_identifiants($u, $p)) { // ← remplace la comparaison hardcodée
        $_SESSION['auth'] = true;
        $_SESSION['username'] = $u; // ← pratique pour afficher le nom
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    $error = "Identifiants invalides";
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
    <div class="container">
        <section class="card">
            <h1>Connexion</h1>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="post" class="form" style="grid-template-columns: 1fr;">
                <label>
                    Nom d'utilisateur
                    <input type="text" name="username" required autocomplete="username">
                </label>
                <label>
                    Mot de passe
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button class="btn" type="submit">Se connecter</button>
            </form>
        </section>
    </div>
</body>
</html>
