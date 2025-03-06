<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo->exec("SET NAMES 'utf8mb4'");

// Benutzerdaten abrufen
$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Falls kein Profilbild existiert, Standardbild "placeholder.png" verwenden
$profileImage = $user['profile_picture'] ?? 'placeholder.png';

// Eigene Anzeigen abrufen
$stmt = $pdo->prepare("
    SELECT L.id, L.title, L.price, L.price_type, 
    (SELECT I.image_url FROM Images I WHERE I.listing_id = L.id LIMIT 1) AS image_url 
    FROM Listings L WHERE user_id = ? AND active = 1");
$stmt->execute([$_SESSION['user_id']]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eigene Chats abrufen
$stmt = $pdo->prepare("SELECT id FROM Chats WHERE user1_id = ? OR user2_id = ?");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Standard-Tab setzen
$tab = $_GET['tab'] ?? 'profil';

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Profil - Nerdeal</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .profile-container {
            display: flex;
            max-width: 1000px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            width: 250px;
            background-color: #f7f7f7;
            padding: 20px;
            border-right: 2px solid #ddd;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            padding: 10px;
            display: block;
        }

        .profile-content {
            flex: 1;
            padding: 30px;
        }

        .profile-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
        }

        .edit-link {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .edit-input {
            display: none;
            margin-top: 5px;
        }

        .listings {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .listing-card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            position: relative;
            text-align: center;
            background-color: #fff;
        }

        .listing-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }

        .delete-icon {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 25px;
            height: 25px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .chat-box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            min-height: 200px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <aside class="sidebar">
        <ul>
            <li><a href="profile.php?tab=profil" class="<?= ($tab == 'profil') ? 'active' : '' ?>">Profil</a></li>
            <li><a href="profile.php?tab=anzeigen" class="<?= ($tab == 'anzeigen') ? 'active' : '' ?>">Anzeigen</a></li>
            <li><a href="profile.php?tab=chats" class="<?= ($tab == 'chats') ? 'active' : '' ?>">Chats</a></li>
        </ul>
    </aside>

    <section class="profile-content">
        <?php if ($tab == 'profil'): ?>
            <h1>Profilinformationen</h1>
            <table class="profile-table">
                <tr>
                    <td>Profilbild</td>
                    <td><img src="<?= htmlspecialchars($profileImage) ?>" alt="Profilbild" class="profile-img"></td>
                    <td><span class="edit-link" onclick="toggleEdit('profile_picture')">Bearbeiten</span></td>
                </tr>
                <tr>
                    <td>Profilname</td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><span class="edit-link" onclick="toggleEdit('username')">Bearbeiten</span></td>
                </tr>
                <tr>
                    <td>E-Mail</td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="edit-link" onclick="toggleEdit('email')">Bearbeiten</span></td>
                </tr>
            </table>

            <div class="edit-input" id="edit-profile_picture">
                <input type="file" id="new-profile_picture" accept="image/*">
                <button onclick="updateProfile('profile_picture')">Speichern</button>
            </div>

            <div class="edit-input" id="edit-username">
                <input type="text" id="new-username" value="<?= htmlspecialchars($user['username']) ?>">
                <button onclick="updateProfile('username')">Speichern</button>
            </div>

            <div class="edit-input" id="edit-email">
                <input type="email" id="new-email" value="<?= htmlspecialchars($user['email']) ?>">
                <button onclick="updateProfile('email')">Speichern</button>
            </div>

        <?php elseif ($tab == 'anzeigen'): ?>
            <h1>Meine Anzeigen</h1>
            <div class="listings">
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-card">
                        <button class="delete-icon" onclick="deleteListing(<?= $listing['id'] ?>)">🗑</button>
                        <img src="<?= htmlspecialchars($listing['image_url'] ?: 'placeholder.png') ?>" alt="Bild">
                        <h3><?= htmlspecialchars($listing['title']) ?></h3>
                        <p>Preis: <?= number_format($listing['price'], 2, ',', '.') ?> €</p>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($tab == 'chats'): ?>
            <h1>Meine Chats</h1>
            <div class="chat-box">
                <?php foreach ($chats as $chat): ?>
                    <p><a href="chat.php?id=<?= $chat['id'] ?>">Chat #<?= $chat['id'] ?></a></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
function toggleEdit(field) {
    $('.edit-input').hide();
    $('#edit-' + field).show();
}

function deleteListing(id) {
    $.post('delete_listing.php', { id: id }, function() {
        location.reload();
    });
}
</script>

</body>
</html>
