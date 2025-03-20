<?php
session_start();
require 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Not authorized.");
}

// Make sure we communicate in UTF-8
$pdo->exec("SET NAMES 'utf8mb4'");

// Retrieve listing ID from GET
$listingId = $_GET['id'] ?? null;
if (!$listingId) {
    die("No listing ID specified.");
}

// Load the listing from the DB, ensuring it belongs to the current user
$stmt = $pdo->prepare("
    SELECT * 
    FROM Listings
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$listingId, $_SESSION['user_id']]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Listing not found or you do not have permission to edit it.");
}

// If user submitted the form to update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Delete listing
    if ($action === 'delete') {
        $stmtDel = $pdo->prepare("
            UPDATE Listings 
            SET active = 0 
            WHERE id = ? AND user_id = ?
        ");
        $stmtDel->execute([$listingId, $_SESSION['user_id']]);
        
        // Redirect back to profile
        header("Location: profile.php?tab=anzeigen");
        exit();
    }
    
    // Otherwise, update listing
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $stmtUpd = $pdo->prepare("
        UPDATE Listings
        SET title = ?, price = ?, description = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmtUpd->execute([
        $title,
        $price,
        $description,
        $listingId,
        $_SESSION['user_id']
    ]);

    // Redirect back to profile after updating
    header("Location: profile.php?tab=anzeigen");
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Anzeige bearbeiten</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type=text],
        input[type=number],
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        textarea {
            height: 100px;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        button {
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .save-btn {
            background: #3498db;
            color: white;
        }
        .save-btn:hover {
            background: #2980b9;
        }
        .delete-btn {
            background: red;
            color: white;
        }
        .delete-btn:hover {
            background: #cc0000;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Anzeige bearbeiten</h1>
    <form method="POST">
        <label for="title">Titel:</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($listing['title']) ?>" required>

        <label for="price">Preis (EUR):</label>
        <input type="number" step="0.01" id="price" name="price" 
               value="<?= htmlspecialchars($listing['price']) ?>" required>

        <label for="description">Beschreibung:</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($listing['description']) ?></textarea>

        <div class="buttons">
            <button type="submit" name="action" value="update" class="save-btn">
                Speichern
            </button>
            <button type="submit" name="action" value="delete" class="delete-btn">
                Löschen
            </button>
        </div>
    </form>
</div>
</body>
</html>
