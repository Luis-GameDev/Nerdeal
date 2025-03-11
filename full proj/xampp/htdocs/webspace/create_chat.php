<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Not authorized.");
}

if (!isset($_POST['seller_id'])) {
    die("No seller specified.");
}

$buyer_id = $_SESSION['user_id'];
$seller_id = $_POST['seller_id'];

if ($buyer_id == $seller_id) {
    die("You cannot chat with yourself.");
}

$stmt = $pdo->prepare("
    SELECT id FROM chats
    WHERE (user1_id = :buyer AND user2_id = :seller)
       OR (user1_id = :seller AND user2_id = :buyer)
");
$stmt->execute([
    ':buyer' => $buyer_id,
    ':seller' => $seller_id
]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if ($chat) {
    header("Location: chat.php?id=" . $chat['id']);
    exit();
}

$stmt = $pdo->prepare("
    INSERT INTO chats (user1_id, user2_id, created_at)
    VALUES (:buyer, :seller, NOW())
");
$stmt->execute([
    ':buyer' => $buyer_id,
    ':seller' => $seller_id
]);
$newChatId = $pdo->lastInsertId();

header("Location: chat.php?id={$newChatId}");
exit();
