<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Chat – Demo mit Foto-Button</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0; 
      padding: 0;
    }

    .chat-container {
      width: 100%;
      max-width: 600px;
      height: 600px; /* Feste Höhe des Chat-Fensters */
      margin: 40px auto;
      background: #fff;
      border-radius: 8px;
      display: flex;
      flex-direction: column;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .chat-header {
      background: #3498db;
      color: #fff;
      padding: 10px;
      border-radius: 8px 8px 0 0;
      text-align: center;
    }

    .chat-messages {
      flex: 1; /* Nimmt den verbleibenden Platz ein */
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
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      margin-right: 10px;
    }

    .chat-input button:last-child {
      margin-right: 0;
    }

    .chat-input button:hover {
      background: #2980b9;
    }

    /* Verstecktes File-Input */
    #photoInput {
      display: none;
    }
  </style>
</head>
<body>
  <div class="chat-container">
    <div class="chat-header">
      <h2>Chat-Demo</h2>
    </div>

    <div class="chat-messages" id="chatMessages">
      <!-- Beispiel-Nachrichten -->
      <div class="message theirs">
        Hallo! Wie kann ich helfen?
        <span class="timestamp">12:00</span>
      </div>
      <div class="message mine">
        Ich schaue mich nur um.
        <span class="timestamp">12:01</span>
      </div>
    </div>

    <div class="chat-input">
      <!-- Verstecktes Input für Fotos -->
      <input type="file" id="photoInput" accept="image/*" multiple>

      <!-- Foto-Button, löst das File-Picker-Fenster aus -->
      <button id="photoBtn">Foto</button>

      <!-- Text-Eingabe -->
      <input type="text" id="chatInput" placeholder="Nachricht eingeben..." />

      <!-- Senden-Button -->
      <button id="sendBtn">Senden</button>
    </div>
  </div>

  <script>
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const photoBtn = document.getElementById('photoBtn');
    const photoInput = document.getElementById('photoInput');

    // Funktion, um Uhrzeit als HH:MM zurückzugeben
    function getTimeStamp() {
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes}`;
    }

    // Neue Nachricht in den Chat einfügen
    function addMessage(text, sender = 'mine') {
      const newMsg = document.createElement('div');
      newMsg.classList.add('message', sender);
      newMsg.innerHTML = `
        ${text}
        <span class="timestamp">${getTimeStamp()}</span>
      `;
      chatMessages.appendChild(newMsg);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Senden-Button
    sendBtn.addEventListener('click', () => {
      const msg = chatInput.value.trim();
      if (msg !== '') {
        addMessage(msg, 'mine');
        chatInput.value = '';
      }
    });

    // Enter-Taste -> Klick auf Senden
    chatInput.addEventListener('keyup', (e) => {
      if (e.key === 'Enter') {
        sendBtn.click();
      }
    });

    // Klick auf Foto-Button -> verstecktes File-Input öffnen
    photoBtn.addEventListener('click', () => {
      photoInput.click();
    });

    // Beispiel: Dateien ausgewählt
    photoInput.addEventListener('change', () => {
      const files = photoInput.files;
      if (files.length > 0) {
        // Hier könntest du die Files hochladen oder anzeigen.
        // Wir zeigen nur eine kurze Nachricht an.
        addMessage(`[${files.length} Foto(s) ausgewählt]`, 'mine');
      }
    });
  </script>
</body>
</html>
