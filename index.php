<?php
session_start();
require 'config.php';

$loggedIn = isset($_SESSION['user_id']);

if (!$loggedIn) {
    $stmt = $pdo->query("SELECT id, title, price FROM Listings WHERE active = 1 ORDER BY RAND() LIMIT 10");
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nerdeal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>Nerdeal</h1>
            </div>
            <div class="search-bar">
                <input type="text" id="search" placeholder="Suche...">
                <button onclick="searchItems()">Suchen</button>
            </div>
            <nav>
                <?php if ($loggedIn): ?>
                    <a href="listings.php">Inserieren</a>
                    <a href="profile.php">Mein Profil</a>
                    <a href="logout.php">Abmelden</a>
                <?php else: ?>
                    <a href="register.php">Registrieren</a>
                    <a href="login.php">Anmelden</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <h2>Entdecke tolle Angebote</h2>
        <div class="listings">
            <?php if (!$loggedIn): ?>
                <?php foreach ($listings as $item): ?>
                    <div class="listing">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p>Preis: <?php echo number_format($item['price'], 2, ',', '.'); ?> €</p>
                        <a href="listing.php?id=<?php echo $item['id']; ?>">Mehr erfahren</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Willkommen zurück! Hier könnten personalisierte Angebote erscheinen.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>
