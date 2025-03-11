<?php
session_start();
require 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['pswd_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Ungültiger Benutzername oder Passwort.";
        }
    } catch (PDOException $e) {
        $error = "Fehler beim Anmelden: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mein Marktplatz</title>
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
            <h2>Login</h2>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="submit-button">Einloggen</button>
            </form>

            <p>Noch keinen Account? <a href="register.php">Jetzt registrieren</a></p>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>
