const express = require('express');
const http = require('http');
const socketIO = require('socket.io');

// App setup
const app = express();
const server = http.createServer(app);
const io = socketIO(server);

// Static files
app.use(express.static(__dirname + '/HTML/admin'));
app.use('/JavaScript', express.static(__dirname + '/JavaScript'));

// Socket setup
io.on('connection', (socket) => {
  console.log('New user connected');

  socket.on('chat message', (msg) => {
    io.emit('chat message', msg); // Emits the message to all connected clients
  });

  socket.on('disconnect', () => {
    console.log('User disconnected');
  });
});

// Server listening
server.listen(3000, () => {
  console.log('listening on *:5500');
});
