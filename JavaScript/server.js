const express = require('express');
const http = require('http');
const socketIO = require('socket.io');
const mysql = require('mysql2/promise');

// App setup
const app = express();
const server = http.createServer(app);
const io = socketIO(server);

// Database setup
const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: 'j&hghasfdk(&5H53HG&^8&*%^$&jnb%&*(&^%$hFGHJKJHGFCV234567%&%',
    database: 'css_game_theory'
});

// Static files
app.use(express.static(__dirname + '/HTML/admin'));
app.use('/JavaScript', express.static(__dirname + '/JavaScript'));

// Socket setup
io.on('connection', (socket) => {
    console.log('New user connected');

    // Handle incoming chat messages
    socket.on('chat message', async (msg) => {
        // Emit message to all connected clients
        io.emit('chat message', msg);

        // Save message to database
        try {
            await db.execute(`
                INSERT INTO chat_messages (message, timestamp)
                VALUES (?, NOW())
            `, [msg]);
            console.log("Message saved to database.");
        } catch (error) {
            console.error("Error saving message to database:", error);
        }
    });

    // Handle user disconnect
    socket.on('disconnect', () => {
        console.log('User disconnected');
    });
});

// Server listening
server.listen(5500, () => {
    console.log('listening on *:5500');
});
