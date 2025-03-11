<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Nicht autorisiert.");
}

$title = $_POST['title'];
$category = $_POST['category'];
$price = $_POST['price'];
$price_type = $_POST['price_type'];
$description = $_POST['description'];
$plz = $_POST['plz'] ?? null;
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("INSERT INTO Listings (user_id, title, category_id, price, price_type, description, plz, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $title, $category, $price, $price_type, $description, $plz]);
    $listing_id = $pdo->lastInsertId();

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['images']['error'][$index] === 0) {
                $uploadDir = 'user-images/';
                $uniqueName = uniqid() . '_' . $_FILES['images']['name'][$index];
                $imagePath = $uploadDir . $uniqueName;

                move_uploaded_file($tmpName, $imagePath);

                $stmt = $pdo->prepare("INSERT INTO Images (listing_id, image_url) VALUES (?, ?)");
                $stmt->execute([$listing_id, $imagePath]);
            }
        }
    }

    header("Location: index.php");
    exit();
} catch (PDOException $e) {
    die("Fehler beim Speichern der Anzeige: " . $e->getMessage());
}
