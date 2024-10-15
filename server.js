const WebSocket = require('ws');
const express = require('express');
const app = express();

// Serve static files (e.g., your frontend HTML) from the 'public' folder
app.use(express.static('public'));

// Start the WebSocket server
const wss = new WebSocket.Server({ port: 3000 });

wss.on('connection', function connection(ws) {
  console.log('New client connected');

  // Handle incoming messages from the client
  ws.on('message', function incoming(message) {
    console.log('Received:', message);

    // Broadcast the message to all clients (including admin)
    wss.clients.forEach(function each(client) {
      if (client.readyState === WebSocket.OPEN) {
        client.send(message);
      }
    });
  });

  // Handle client disconnecting
  ws.on('close', function close() {
    console.log('Client disconnected');
  });
});

console.log('WebSocket server is running on ws://localhost:3000');
