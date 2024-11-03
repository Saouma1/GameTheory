// Import MySQL and create a connection pool
const mysql = require('mysql2/promise');

// Database configuration
const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: 'j&hghasfdk(&5H53HG&^8&*%^$&jnb%&*(&^%$hFGHJKJHGFCV234567%&%',
    database: 'css_game_theory'
});

let usernameValue = "";
let passwordValue = ""; // very secure, don't worry about it

// Function to submit form data to the database
async function submitForm() {
    if (document.getElementById("username") != null) {
        usernameValue = document.getElementById('username').value;
    } else {
        usernameValue = "submitForm() NULL";
    }

    if (document.getElementById("password") != null) {
        passwordValue = document.getElementById('password').value;
    } else {
        passwordValue = "submitForm() NULL";
    }

    try {
        // Insert the form data into the database
        await db.execute(`
            INSERT INTO user_data (username, password)
            VALUES (?, ?)
        `, [usernameValue, passwordValue]);
        console.log("Data submitted successfully.");
    } catch (error) {
        console.error("Failed to submit data:", error);
    }
}

// Function to log user information from the database
async function logInfo() {
    try {
        // Retrieve data from the database
        const [rows] = await db.query(`
            SELECT username, password FROM user_data
            ORDER BY id DESC LIMIT 1
        `);
        if (rows.length > 0) {
            console.log("Username:", rows[0].username);
            console.log("Password:", rows[0].password);
        } else {
            console.log("No data found in the database.");
        }
    } catch (error) {
        console.error("Error retrieving data:", error);
    }
}

// Adjust logo and cross styles if elements exist
if (document.getElementById("courseLogo")) {
    let cssCrossLeft = document.getElementById("scholasticaCrossoutLeft");
    let cssCrossRight = document.getElementById("scholasticaCrossoutRight");
    let courseCrossLeft = document.getElementById("courseCrossoutLeft");
    let courseCrossRight = document.getElementById("courseCrossoutRight");
    let cssLogo = document.getElementById("scholasticaLogo");
    let courseLogo = document.getElementById("courseLogo");

    let clw = cssLogo.offsetWidth; // css logo width
    let clh = cssLogo.offsetHeight; // css logo height
    let cll = cssLogo.offsetLeft; // css logo left
    let clt = cssLogo.offsetTop; // css logo top
    let clw2 = courseLogo.offsetWidth; // course logo width
    let clh2 = courseLogo.offsetHeight; // course logo height
    let cll2 = courseLogo.offsetLeft; // course logo left
    let clt2 = courseLogo.offsetTop; // course logo top
    let borderRadius = 10; // border radius of 10px; static

    cssCrossLeft.style.width = (clw * 1.375) + "px";
    cssCrossLeft.style.left = (cll + (borderRadius / 4)) + "px";
    cssCrossLeft.style.top = (clt + (borderRadius / 4)) + "px";
    cssCrossRight.style.width = (clw * 1.375) + "px";
    cssCrossRight.style.left = (cll + (borderRadius / 4)) + "px";
    cssCrossRight.style.top = (clt + (borderRadius / 4)) + "px";

    courseCrossLeft.style.width = (clw2 * 1.375) + "px";
    courseCrossLeft.style.left = (cll2 + (borderRadius / 4)) + "px";
    courseCrossLeft.style.top = (clt2 + (borderRadius / 4)) + "px";
    courseCrossRight.style.width = (clw2 * 1.375) + "px";
    courseCrossRight.style.left = (cll2 + (borderRadius / 4)) + "px";
    courseCrossRight.style.top = (clt2 + (borderRadius / 4)) + "px";
}
