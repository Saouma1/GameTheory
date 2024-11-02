// Import MySQL and create a connection pool
const mysql = require('mysql2/promise');

// Create connection to the database
const db = mysql.createPool({
    host: 'localhost',
    user: 'your_username',
    password: 'your_password',
    database: 'your_database'
});

// Function to retrieve and display game information
async function displayInfoInformation() {
    try {
        // Query the database for game information
        const [rows] = await db.execute(`
            SELECT gameName, gameDate, playerNum
            FROM games
            WHERE playerID = ?
        `, [playerID]); // Replace `playerID` with the appropriate identifier variable

        // Assuming one result (rows[0]), use the result data to populate HTML elements
        if (rows.length > 0) {
            const data = rows[0];
            if (data.gameName) document.getElementById('firstNameDisplay').innerHTML = data.gameName;
            if (data.gameDate) document.getElementById('lastNameDisplay').innerHTML = data.gameDate;
            if (data.playerNum) document.getElementById('emailDisplay').innerHTML = data.playerNum;
        } else {
            console.log("No game data found for the provided player ID.");
        }

    } catch (error) {
        console.error("Error fetching game data:", error);
    }
}
