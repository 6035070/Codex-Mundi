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

// Registratie verwerking
if ($_POST['action'] ?? '' === 'register') {
    try {
        $data = array(
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? ''
        );
        
        $userManager->register($data);
        $success = 'Registratie succesvol! Je kunt nu inloggen.';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Codex Mundi</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="auth-container">
            <!-- Registratie Form -->
            <div class="auth-form">
                <h2>Registreren</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label>Gebruikersnaam:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Voornaam:</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Achternaam:</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Wachtwoord:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit">Registreren</button>
                </form>
                <p class="auth-switch">
                    Al een account? <a href="login.php">Inloggen</a>
                </p>
            </div>
        </div>
        
        <p><a href="index.php">← Terug naar hoofdpagina</a></p>
    </div>
</body>
</html>