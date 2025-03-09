const WebSocket = require('ws');
const mysql = require('mysql2');

const wss = new WebSocket.Server({ port: 8080 });

const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',       
  database: 'db_nerdeal'  
});
db.connect();

// This object will map userIds to open sockets
const clients = {};

wss.on('connection', (ws, req) => {
  const userId = new URL(req.url, 'http://localhost').searchParams.get('user');
  if (userId) {
    clients[userId] = ws;
  }

  ws.on('message', (message) => {
    const data = JSON.parse(message);

    if (data.type === 'chatMessage') {
      // Save message to DB
      const { chatId, senderId, content } = data;
      db.query(
        "INSERT INTO messages (chat_id, sender_id, content) VALUES (?, ?, ?)",
        [chatId, senderId, content],
        (err, result) => {
          if (err) {
            console.error("Error saving message:", err);
            return;
          }
          // Forward the message to the recipient if online
          if (clients[data.recipientId]) {
            clients[data.recipientId].send(JSON.stringify({
              type: 'chatMessage',
              chatId,
              senderId,
              content,
              timestamp: new Date().toISOString()
            }));
          }
        }
      );
    }
  });

  ws.on('close', () => {
    if (userId && clients[userId] === ws) {
      delete clients[userId];
    }
  });
});

console.log("WebSocket server running on ws://localhost:8080");
