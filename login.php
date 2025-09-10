<?php
require_once 'includes/user.php';

$userManager = new UserManager();
$error = '';
$success = '';

// Als al ingelogd, redirect naar index
if ($userManager->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Login verwerking
if ($_POST['action'] ?? '' === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($userManager->login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Ongeldige gebruikersnaam of wachtwoord';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Codex Mundi</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="auth-container">
            <!-- Login Form -->
            <div class="auth-form">
                <h2>Inloggen</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label>Gebruikersnaam of Email:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Wachtwoord:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit">Inloggen</button>
                </form>
                <p class="auth-switch">
                    Nog geen account? <a href="register.php">Registreer hier</a>
                </p>
            </div>
        </div>
        
        <p><a href="index.php">← Terug naar hoofdpagina</a></p>
    </div>
</body>
</html>