<?php
session_start();
require 'config.php';

if (!isset($_GET['id'])) {
    die("Anzeige nicht gefunden.");
}
$pdo->exec("SET NAMES 'utf8mb4'");

$listing_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT L.*, 
           C.name AS category_name, 
           U.username, U.id AS seller_id, U.profile_picture, U.email
    FROM Listings L
    JOIN user U ON L.user_id = U.id
    JOIN Categories C ON L.category_id = C.id
    WHERE L.id = ?");
$stmt->execute([$listing_id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Anzeige existiert nicht.");
}

$stmt = $pdo->prepare("SELECT image_url FROM Images WHERE listing_id = ?");
$stmt->execute([$listing_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
$imageUrls = array_column($images, 'image_url');

if (empty($imageUrls)) {
    $imageUrls[] = 'placeholder.png';
}

function translatePriceType($type) {
    switch ($type) {
        case 'fixed': return 'Festpreis';
        case 'negotiable': return 'Verhandlungsbasis';
        case 'free': return 'Zu verschenken';
        default: return 'Unbekannt';
    }
}

$chat_id = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $listing['seller_id']) {
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
    $stmt->execute([$_SESSION['user_id'], $listing['seller_id'], $listing['seller_id'], $_SESSION['user_id']]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chat) {
        $chat_id = $chat['id'];
    }
}

$sellerImage = $listing['profile_picture'] ?? 'placeholder.png';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($listing['title']) ?> - Nerdeal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .back-button {
            text-decoration: none;
            color: #3498db;
            font-size: 18px;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .back-button:hover {
            text-decoration: underline;
        }

        .listing-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .price {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }

        .description {
            margin-top: 15px;
            line-height: 1.6;
        }

        .listing-info {
            margin-top: 15px;
            font-size: 16px;
            color: #555;
        }

        .seller-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background-color: #f1f1f1;
            border-radius: 10px;
            margin-top: 20px;
        }

        .seller-details {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .seller-box img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
        }

        .chat-button-wrapper {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            width: auto;
        }

        .chat-button-wrapper form {
            display: flex;
            align-items: center;
        }

        .chat-icon {
            background-color: #3498db;
            color: white;
            padding: 10px;
            border-radius: 50%;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            transition: background 0.2s ease-in-out;
        }

        .chat-icon:hover {
            background-color: #2980b9;
        }

        .slideshow {
            position: relative;
            width: 100%;
            max-width: 600px;
            height: 400px; 
            margin: 20px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f1f1f1; 
            border-radius: 10px;
        }

        .slideshow img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            border-radius: 10px;
        }

        .slideshow .prev, .slideshow .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 10px;
            cursor: pointer;
            border-radius: 50%;
            font-size: 20px;
        }

        .slideshow .prev { left: 10px; }
        .slideshow .next { right: 10px; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="back-button">⬅ Zurück</a>

    <div class="listing-header">
        <h1><?= htmlspecialchars($listing['title']) ?></h1>
        <span class="price"><?= number_format($listing['price'], 2, ',', '.') ?> €</span>
    </div>

    <p class="description"><?= nl2br(htmlspecialchars($listing['description'])) ?></p>

    <p class="listing-info"><strong>Kategorie:</strong> <?= htmlspecialchars($listing['category_name']) ?></p>
    <p class="listing-info"><strong>Preisart:</strong> <?= translatePriceType($listing['price_type']) ?></p>
    <p class="listing-info"><strong>Standort:</strong> <?= htmlspecialchars($listing['plz'] ?? 'Unbekannt') ?></p>

    <div class="slideshow">
        <img id="slide" src="<?= htmlspecialchars($imageUrls[0]) ?>" alt="Anzeigenbild">
        <?php if (count($imageUrls) > 1): ?>
            <span class="prev" onclick="prevSlide()">❮</span>
            <span class="next" onclick="nextSlide()">❯</span>
        <?php endif; ?>
    </div>

    <div class="seller-box">
        <div class="seller-details">
            <img src="<?= htmlspecialchars($sellerImage) ?>" alt="Profilbild">
            <span><strong>Verkäufer:</strong> <?= htmlspecialchars($listing['username']) ?></span>
        </div>

        <div class="chat-button-wrapper">
            <form action="create_chat.php" method="POST">
                <input type="hidden" name="seller_id" value="<?= $listing['seller_id'] ?>">
                <button type="submit" class="chat-icon">💬</button>
            </form>
        </div>
    </div>
</div>

<script>
let images = <?= json_encode($imageUrls) ?>;
let index = 0;
function prevSlide() { index = (index - 1 + images.length) % images.length; document.getElementById("slide").src = images[index]; }
function nextSlide() { index = (index + 1) % images.length; document.getElementById("slide").src = images[index]; }
</script>

</body>
</html>
