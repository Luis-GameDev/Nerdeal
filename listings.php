<?php
session_start();
require 'config.php';

$pdo->exec("SET NAMES 'utf8mb4'");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT id, name FROM Categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anzeige aufgeben - Nerdeal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>Nerdeal</h1>
            </div>
            <nav>
                <a href="index.php">Startseite</a>
                <a href="profile.php">Mein Profil</a>
                <a href="logout.php">Abmelden</a>
            </nav>
        </div>
    </header>

    <main>
        <h2>Anzeige aufgeben</h2>
        <form action="process_listings.php" method="POST" enctype="multipart/form-data">
            <label for="title">Titel:</label>
            <input type="text" id="title" name="title" required>

            <label for="category">Kategorie:</label>
            <select id="category" name="category" required>
                <option value="">Wähle eine Kategorie</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="price">Preis:</label>
            <input type="number" id="price" name="price" min="0" step="0.01" required>
            <select name="price_type">
                <option value="fixed">Festpreis</option>
                <option value="negotiable">Verhandlungsbasis</option>
                <option value="free">Zu verschenken</option>
            </select>

            <label for="description">Beschreibung:</label>
            <textarea id="description" name="description" maxlength="4000" required></textarea>

            <label for="images">Bilder (empfohlen):</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">

            <label for="plz">Postleitzahl/Stadt:</label>
            <input type="text" id="plz" name="plz" placeholder="Optional">

            <button type="submit">Anzeige veröffentlichen</button>
        </form>
    </main>
</body>
</html>
