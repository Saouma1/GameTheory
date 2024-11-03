// Import MySQL and create a connection pool
const mysql = require('mysql2/promise');

// Create connection to the database
const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: 'j&hghasfdk(&5H53HG&^8&*%^$&jnb%&*(&^%$hFGHJKJHGFCV234567%&%',
    database: 'css_game_theory'
});

// Function to retrieve and display student information based on URL parameters
async function displayInfo() {
    // Get URL parameters (assuming the URL structure is consistent with fname and lname parameters)
    const urlFull = window.location.href;
    const urlFnameIndex = urlFull.search("fname=");
    const urlNames = urlFull.slice(urlFnameIndex + 6);
    const urlFNameEndIndex = urlNames.search("&");
    const urlFName = urlNames.substring(0, urlFNameEndIndex);
    const urlLNameIndex = urlNames.search("=");
    const urlLName = urlNames.slice(urlLNameIndex + 1);

    try {
        // Query the database for student information
        const [rows] = await db.execute(`
            SELECT email, redBlackNum, redBlackHigh, redBlackLow, redBlackGPA,
                   wheatSteelNum, wheatHigh, wheatGoalNum, steelHigh, steelGoalNum
            FROM students
            WHERE firstName = ? AND lastName = ?
        `, [urlFName, urlLName]);

        // Assuming one result (rows[0]), use the result data to populate HTML elements
        if (rows.length > 0) {
            const data = rows[0];
            document.getElementById('studentHeader').innerHTML = `Student (${urlFName} ${urlLName})`;
            document.getElementById('firstNameDisplay').innerHTML = urlFName;
            document.getElementById('lastNameDisplay').innerHTML = urlLName;
            
            document.getElementById('emailDisplay').innerHTML = data.email;
            document.getElementById('redBlackNum').innerHTML = data.redBlackNum;
            document.getElementById('redBlackHigh').innerHTML = data.redBlackHigh;
            document.getElementById('redBlackLow').innerHTML = data.redBlackLow;
            document.getElementById('redBlackGPA').innerHTML = data.redBlackGPA;
            
            document.getElementById('wheatSteelNum').innerHTML = data.wheatSteelNum;
            document.getElementById('wheatHigh').innerHTML = data.wheatHigh;
            document.getElementById('wheatGoalNum').innerHTML = data.wheatGoalNum;
            document.getElementById('steelHigh').innerHTML = data.steelHigh;
            document.getElementById('steelGoalNum').innerHTML = data.steelGoalNum;
        } else {
            console.log("No student data found for the provided name.");
        }

    } catch (error) {
        console.error("Error fetching data:", error);
    }
}