<?php
session_start();
require 'config.php';  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Die Passwörter stimmen nicht überein.";
    } else {
        $pswd_hash = password_hash($password, PASSWORD_DEFAULT);

        // add email validation using MercuryMail and PHPMailer here later
        try {
            $stmt = $pdo->prepare("INSERT INTO user (username, email, pswd_hash) VALUES (?, ?, ?)");
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $pswd_hash);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Fehler bei der Registrierung. Bitte versuche es später erneut.";
            }
        } catch (PDOException $e) {
            $error = "Fehler bei der Registrierung: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren - Nerdeal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>Nerdeal</h1>
            </div>
            <nav>
                <a href="index.php">Zurück zum Marktplatz</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="form-container">
            <h2>Registrieren</h2>
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif ?>

            <form action="register.php" method="POST">
                <div class="input-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="email">E-Mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Passwort bestätigen</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="submit-button">Registrieren</button>
            </form>
            <p>Bereits ein Konto? <a href="login.php">Jetzt einloggen</a></p>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>
