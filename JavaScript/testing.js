// server.js
const path = require('path');
const express = require('express');
const app = express();
const http = require('http').Server(app);
const { Server } = require("socket.io");
const io = new Server(http);


// Serve your static files (e.g., HTML, JS, CSS)
app.use('/HTML', express.static(path.join(__dirname, 'HTML')));

  
io.on('connection', (socket) => {
  console.log('a user connected');
  
  socket.on('test', (message) => {
    console.log(message);
  });

  socket.on('disconnect', () => {
    console.log('user disconnected');
  });
});

http.listen(5500, () => {
  console.log('listening on *:5500');
});
