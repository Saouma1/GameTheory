// Import MySQL and create a connection pool
const mysql = require('mysql2/promise');

// Create connection to the database
const db = mysql.createPool({
    host: 'localhost',
    user: 'your_username',
    password: 'your_password',
    database: 'your_database'
});

// Function to display Red/Black game info from the database
async function displayInfoRedBlack() {
    try {
        // Retrieve game details from the URL
        const url = new URL(window.location.href);
        const gameDate = url.searchParams.get("d");
        const playerOne = url.searchParams.get("p1");
        const playerTwo = url.searchParams.get("p2");

        // Query database for player and game details
        const [rows] = await db.execute(`
            SELECT id, card_type
            FROM red_black_game_cards
            WHERE game_date = ? AND (player_name = ? OR player_name = ?)
        `, [gameDate, playerOne, playerTwo]);

        // Update HTML elements with player and game data
        document.getElementById('playerOneName').innerHTML = playerOne;
        document.getElementById('headerPlayerOne').innerHTML = playerOne;
        document.getElementById('tablePlayerOneCard').innerHTML = playerOne;
        document.getElementById('tablePlayerOnePoints').innerHTML = playerOne;
        document.getElementById('playerTwoName').innerHTML = playerTwo;
        document.getElementById('headerPlayerTwo').innerHTML = playerTwo;
        document.getElementById('tablePlayerTwoCard').innerHTML = playerTwo;
        document.getElementById('tablePlayerTwoPoints').innerHTML = playerTwo;
        document.getElementById('gameDate').innerHTML = gameDate;

        // Update card colors based on database results
        rows.forEach(card => {
            const cardElement = document.getElementById(card.id);
            if (card.card_type === "R") {
                cardElement.style.backgroundColor = "pink";
            } else if (card.card_type === "B") {
                cardElement.style.backgroundColor = "gray";
            }
        });

    } catch (error) {
        console.error("Error retrieving Red/Black game info:", error);
    }
}


async function displayInfoWheatSteel() {
    try {
        // Retrieve game details from the URL
        const url = new URL(window.location.href);
        const gameDate = url.searchParams.get("date");
        const gameTeam = decodeURIComponent(url.searchParams.get("t"));

        // Query database for Wheat/Steel rounds and trades
        const [rows] = await db.execute(`
            SELECT round_num, wheat_count, steel_count, wheat_trade, steel_trade, wheat_consume, steel_consume
            FROM wheat_steel_game_rounds
            WHERE game_date = ? AND team_name = ?
            ORDER BY round_num
        `, [gameDate, gameTeam]);

        // Update game date and team in HTML
        document.getElementById('gameDate').innerHTML = gameDate;
        document.getElementById('teamName').innerHTML = gameTeam;

        // Iterate over each round result and update HTML elements accordingly
        rows.forEach((round, index) => {
            const roundNumber = index + 1;

            // Wheat data for rounds
            document.getElementById(`roundOneWheat`).innerHTML = round.wheat_count;
            document.getElementById(`roundTwoWheat`).innerHTML = round.wheat_trade;
            document.getElementById(`roundThreeWheat`).innerHTML = round.wheat_consume;

            // Steel data for rounds
            document.getElementById(`roundOneSteel`).innerHTML = round.steel_count;
            document.getElementById(`roundTwoSteel`).innerHTML = round.steel_trade;
            document.getElementById(`roundThreeSteel`).innerHTML = round.steel_consume;
        });

    } catch (error) {
        console.error("Error retrieving Wheat/Steel game info:", error);
    }
}