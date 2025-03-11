<?php
session_start();
require 'config.php';

$pdo->exec("SET NAMES 'utf8mb4'");

$loggedIn = isset($_SESSION['user_id']);

$catStmt = $pdo->query("SELECT id, name FROM Categories ORDER BY name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$catFilter   = $_GET['category'] ?? null;
$searchParam = $_GET['search']   ?? null;

$sql = "
    SELECT
        L.id,
        L.title,
        L.description,
        L.price,
        L.price_type,
        C.name AS category_name,
        (
            SELECT I.image_url
            FROM Images I
            WHERE I.listing_id = L.id
            LIMIT 1
        ) AS image_url
    FROM Listings L
    JOIN Categories C ON L.category_id = C.id
    WHERE L.active = 1
";

$params = [];

if ($catFilter) {
    $sql .= " AND L.category_id = :catId";
    $params[':catId'] = $catFilter;
}

if ($searchParam) {
    $sql .= " 
      AND (
            L.title LIKE :searchTerm 
            OR L.description LIKE :searchTerm
            OR C.name LIKE :searchTerm
          )
    ";
    $params[':searchTerm'] = '%' . $searchParam . '%';
}

$sql .= " ORDER BY RAND() LIMIT 40";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

function translatePriceType($type) {
    switch ($type) {
        case 'fixed':
            return 'Direktkauf';
        case 'negotiable':
            return 'Verhandlungsbasis';
        case 'free':
            return 'Zu verschenken';
        default:
            return 'Unbekannt';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Nerdeal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .wrapper {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
        }
        .sidebar {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            height: 100%;
        }
        .sidebar h3 {
            margin-bottom: 10px;
        }
        .sidebar a {
            display: block;
            margin-bottom: 5px;
            color: #333;
            text-decoration: none;
        }
        .sidebar a:hover {
            text-decoration: underline;
        }
        .content {
            padding: 10px;
        }
        .listings {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .listing-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.1s ease-in-out;
        }
        .listing-card:hover {
            transform: scale(1.02);
        }
        .listing-card img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .listing-card h3 {
            margin-bottom: 8px;
        }
        .listing-card p {
            margin-bottom: 8px;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            background-color: #2c3e50;
            color: white;
        }
        .header-container .logo h1 {
            font-size: 24px;
            margin: 10px 0;
        }
        .search-bar {
            display: flex;
            align-items: center;
        }
        .search-bar input {
            padding: 8px;
            font-size: 16px;
            width: 200px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .search-bar button {
            padding: 8px 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #2980b9;
        }
        nav a {
            margin-left: 20px;
            color: white;
            text-decoration: none;
        }
        nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo">
            <h1>Nerdeal</h1>
        </div>
        <div class="search-bar">
            <input type="text" id="search" placeholder="Suche..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
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

<div class="wrapper">
    <aside class="sidebar">
        <h3>Kategorien</h3>
        <a href="index.php">Alle anzeigen</a>
        <?php foreach ($categories as $cat): ?>
            <a href="index.php?category=<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
        <?php endforeach; ?>
    </aside>

    <div class="content">
        <h2>Entdecke tolle Angebote</h2>
        <div class="listings">
            <?php foreach ($listings as $item): ?>
                <?php $priceLabel = translatePriceType($item['price_type']); ?>
                <a class="listing-card" href="listing.php?id=<?php echo $item['id']; ?>">
                    <?php if (!empty($item['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Angebot">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p>Preis: <?php echo number_format($item['price'], 2, ',', '.'); ?> €</p>
                    <p><?php echo $priceLabel; ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    function searchItems() {
        const query = document.getElementById('search').value.trim();
        if (query) {
            window.location.href = 'index.php?search=' + encodeURIComponent(query);
        } else {
            window.location.href = 'index.php';
        }
    }
</script>

</body>
</html>
