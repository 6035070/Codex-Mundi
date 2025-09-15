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

<body class='popup_geryout'>
    <div class="container">

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="auth-container">
            <!-- Registratie Form -->
            <div class="auth-form blur">
                <h2><b><i>Welkom</i></b> bij Codex Mundi👋</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <input type="text" placeholder='Gebruikersnaam' name="username" required>
                    </div>
                    <div class="form-group">
                        <input type="email" placeholder='Email' name="email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder='Voornaam' name="first_name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder='Achternaam' name="last_name" required>
                    </div>

                    <div class="form-group">
                        <input type="password" placeholder='Wachtwoord' name="password" required>
                    </div>
                    <button type="submit">Registreren</button>
                </form>

                <button><a href="login.php">Inloggen</a></button>
                <button><a href="index.php">⬅️Terug naar hoofdpagina</a></button>
            </div>
        </div>
    </div>
</body>

</html>