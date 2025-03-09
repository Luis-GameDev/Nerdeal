<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Not authorized.");
}

$pdo->exec("SET NAMES 'utf8mb4'");

$chatId = $_GET['id'] ?? null;
if (!$chatId) {
    die("No chat specified.");
}

$stmt = $pdo->prepare("SELECT * FROM chats WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
$stmt->execute([$chatId, $_SESSION['user_id'], $_SESSION['user_id']]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    die("You do not have access to this chat.");
}

$userId = $_SESSION['user_id'];

$partnerId = ($chat['user1_id'] == $userId) ? $chat['user2_id'] : $chat['user1_id'];

$stmtUser = $pdo->prepare("SELECT username FROM user WHERE id = ?");
$stmtUser->execute([$partnerId]);
$partnerRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
$partnerName = $partnerRow ? $partnerRow['username'] : '???';

$stmtMsg = $pdo->prepare("SELECT * FROM messages WHERE chat_id = ? ORDER BY created_at ASC");
$stmtMsg->execute([$chatId]);
$messages = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Nerdeal - Chat</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      font-family: Arial, sans-serif;
      background: #f4f4f4;
    }

    .chat-container {
      display: flex;
      flex-direction: column;
      width: 100%;
      height: 100%;
      background: #fff;
    }

    .chat-header {
      background-color: #2c3e50;
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 30px;  
    }

    .chat-header .logo {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .chat-header .logo h1 {
      font-size: 24px;  
      margin: 0;
    }

    .chat-header .chat-title {
      font-size: 18px;  
      font-weight: normal;
      opacity: 0.9;
    }

    .chat-header nav a {
      color: white;
      text-decoration: none;
      font-size: 16px;
      margin-left: 20px;
    }

    .chat-header nav a:hover {
      text-decoration: underline;
    }

    .chat-messages {
      flex: 1;
      overflow-y: auto;
      padding: 10px;
    }

    .message {
      margin-bottom: 10px;
      padding: 8px 12px;
      border-radius: 6px;
      max-width: 80%;
      word-wrap: break-word;
      clear: both;
      position: relative;
      font-size: 14px;
    }

    .message.mine {
      background: #d1ecf1;
      float: right;
      text-align: right;
    }

    .message.theirs {
      background: #ececec;
      float: left;
      text-align: left;
    }

    .timestamp {
      display: block;
      font-size: 12px;
      color: #888;
      margin-top: 5px;
    }

    .chat-input {
      display: flex;
      align-items: center;
      border-top: 1px solid #ccc;
      padding: 10px;
    }

    .chat-input input[type=text] {
      flex: 1;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-right: 10px;
      font-size: 14px;
    }

    .chat-input button {
      background: #3498db;
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
    }

    .chat-input button:last-child {
      margin-right: 0;
    }

    .chat-input button:hover {
      background: #2980b9;
    }

    .send-icon {
      font-size: 16px;
      margin-left: 4px;
    }

    #photoInput {
      display: none;
    }

    .chat-image {
      max-width: 200px;
      border-radius: 5px;
      display: block;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <div class="chat-container">
    <div class="chat-header">
      <div class="logo">
        <h1>Nerdeal</h1>
        <span class="chat-title">Chat with <?= htmlspecialchars($partnerName) ?></span>
      </div>
      <nav>
        <a href="index.php">Home</a>
        <a href="profile.php">Mein Profil</a>
        <a href="logout.php">Abmelden</a>
      </nav>
    </div>

    <div class="chat-messages" id="chatMessages">
      <?php foreach ($messages as $msg):
          $senderClass = ($msg['sender_id'] == $userId) ? 'mine' : 'theirs';
      ?>
        <div class="message <?= $senderClass ?>">
          <?php 
            $lower = strtolower($msg['content']);
            if (strpos($lower, 'data:image/') === 0 || preg_match('/\\.(png|jpg|jpeg|gif)$/i', $lower)) {
              echo '<img src="' . htmlspecialchars($msg['content']) . '" class="chat-image" alt="Bild">';
            } else {
              echo nl2br(htmlspecialchars($msg['content']));
            }
          ?>
          <span class="timestamp">
            <?= date('H:i', strtotime($msg['created_at'])) ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="chat-input">
      <input type="file" id="photoInput" accept="image/*" multiple>
      <button id="photoBtn">Foto</button>

      <input type="text" id="chatInput" placeholder="Nachricht eingeben..." />
      <button id="sendBtn">
        ➤
      </button>
    </div>
  </div>

  <script>
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const photoBtn = document.getElementById('photoBtn');
    const photoInput = document.getElementById('photoInput');

    const userId = <?= json_encode($userId) ?>;
    const partnerId = <?= json_encode($partnerId) ?>;
    const chatId = <?= json_encode($chatId) ?>;

    const ws = new WebSocket('ws://localhost:8080?user=' + userId);

    ws.onmessage = (event) => {
      const data = JSON.parse(event.data);
      if (data.chatId == chatId) {
        displayMessage(data.content, (data.senderId == userId) ? 'mine' : 'theirs');
      }
    };

    function displayMessage(content, sender='mine') {
      const msgDiv = document.createElement('div');
      msgDiv.classList.add('message', sender);

      const lower = content.toLowerCase();
      if (lower.startsWith('data:image/') || /\.(png|jpg|jpeg|gif)$/i.test(lower)) {
        msgDiv.innerHTML = `
          <img src="${content}" class="chat-image" alt="Bild">
          <span class="timestamp">${currentTime()}</span>
        `;
      } else {
        msgDiv.innerHTML = `
          ${content}
          <span class="timestamp">${currentTime()}</span>
        `;
      }
      chatMessages.appendChild(msgDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function currentTime() {
      const now = new Date();
      return now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }

    function sendMessage() {
      const msg = chatInput.value.trim();
      if (msg !== '') {
        displayMessage(msg, 'mine');

        ws.send(JSON.stringify({
          type: 'chatMessage',
          chatId: chatId,
          senderId: userId,
          recipientId: partnerId,
          content: msg
        }));

        chatInput.value = '';
      }
    }

    sendBtn.addEventListener('click', () => {
      sendMessage();
    });

    chatInput.addEventListener('keyup', (e) => {
      if (e.key === 'Enter') {
        sendMessage();
      }
    });

    photoBtn.addEventListener('click', () => {
      photoInput.click();
    });

    photoInput.addEventListener('change', () => {
      const files = photoInput.files;
      if (!files.length) return;

      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = function(e) {
          const dataUrl = e.target.result;
          displayMessage(dataUrl, 'mine');
          ws.send(JSON.stringify({
            type: 'chatMessage',
            chatId: chatId,
            senderId: userId,
            recipientId: partnerId,
            content: dataUrl
          }));
        };
        reader.readAsDataURL(file);
      }
    });

    chatMessages.scrollTop = chatMessages.scrollHeight;
  </script>
</body>
</html>
