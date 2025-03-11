<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo->exec("SET NAMES 'utf8mb4'");

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM user WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profileImage = $user['profile_picture'] ?? 'placeholder.png';

$stmt = $pdo->prepare("
    SELECT L.id, L.title, L.price, L.price_type, 
           (SELECT I.image_url FROM Images I WHERE I.listing_id = L.id LIMIT 1) AS image_url 
    FROM Listings L
    WHERE user_id = ? AND active = 1
");
$stmt->execute([$userId]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM chats WHERE user1_id = ? OR user2_id = ?");
$stmt->execute([$userId, $userId]);
$allChats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tab = $_GET['tab'] ?? 'profil';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mein Profil - Nerdeal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        header {
            background-color: #2c3e50;
            color: #fff;
            padding: 20px;
        }
        .header-container {
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-container .logo h1 {
            font-size: 24px;
            margin: 0;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            margin-left: 20px;
        }
        nav a:hover {
            text-decoration: underline;
        }

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
        .sidebar ul li a:hover {
            background-color: #eaeaea;
        }
        .sidebar ul li a.active {
            font-weight: bold;
            text-decoration: underline;
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

        .chat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        .chat-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: box-shadow 0.2s ease;
        }
        .chat-card:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }
        .chat-card h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .chat-card p {
            margin-bottom: 6px;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>Nerdeal</h1>
            </div>
            <nav>
                <a href="index.php">Startseite</a>
                <a href="logout.php">Abmelden</a>
            </nav>
        </div>
    </header>

    <div class="profile-container">
        <aside class="sidebar">
            <ul>
                <li>
                    <a href="profile.php?tab=profil" class="<?= ($tab == 'profil') ? 'active' : '' ?>">
                        Profil
                    </a>
                </li>
                <li>
                    <a href="profile.php?tab=anzeigen" class="<?= ($tab == 'anzeigen') ? 'active' : '' ?>">
                        Anzeigen
                    </a>
                </li>
                <li>
                    <a href="profile.php?tab=chats" class="<?= ($tab == 'chats') ? 'active' : '' ?>">
                        Chats
                    </a>
                </li>
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
                <?php
                if (!$allChats) {
                    echo "<p>Keine Chats vorhanden.</p>";
                } else {
                    echo '<div class="chat-cards">';
                    foreach ($allChats as $chatRow) {
                        $partner = ($chatRow['user1_id'] == $userId) ? $chatRow['user2_id'] : $chatRow['user1_id'];

                        $stmtPartner = $pdo->prepare("SELECT username FROM user WHERE id = ?");
                        $stmtPartner->execute([$partner]);
                        $partnerInfo = $stmtPartner->fetch(PDO::FETCH_ASSOC);
                        $partnerName = $partnerInfo ? $partnerInfo['username'] : '???';

                        $stmtLast = $pdo->prepare("
                            SELECT content, created_at
                            FROM messages
                            WHERE chat_id = ?
                            ORDER BY created_at DESC
                            LIMIT 1
                        ");
                        $stmtLast->execute([$chatRow['id']]);
                        $lastMsg = $stmtLast->fetch(PDO::FETCH_ASSOC);

                        $lastContent = $lastMsg ? $lastMsg['content'] : 'Keine Nachrichten';
                        $lastCreated = $lastMsg ? date('d.m.Y H:i', strtotime($lastMsg['created_at'])) : '';

                        ?>
                        <div class="chat-card" onclick="window.location.href='chat.php?id=<?= $chatRow['id'] ?>'">
                            <h3><?= htmlspecialchars($partnerName) ?></h3>
                            <p>
                                <?php 
                                  $lower = strtolower($lastContent);
                                  if (strpos($lower, 'data:image/') === 0 || preg_match('/\\.(png|jpg|jpeg|gif)$/i', $lower)) {
                                      echo '[Bild]';
                                  } else {
                                      echo htmlspecialchars(mb_strimwidth($lastContent, 0, 40, '...'));
                                  }
                                ?>
                            </p>
                            <?php if ($lastCreated): ?>
                                <p><small><?= $lastCreated ?></small></p>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                    echo '</div>';
                }
                ?>
            <?php endif; ?>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function toggleEdit(field) {
        $('.edit-input').hide();
        $('#edit-' + field).show();
    }

    function updateProfile(field) {
        // add ajax call to update the profile field here
        alert('Updating ' + field + ' ...');
    }

    function deleteListing(id) {
        $.post('delete_listing.php', { id: id }, function() {
            location.reload();
        });
    }
    </script>
</body>
</html>
