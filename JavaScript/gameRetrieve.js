// find max ID in DB

function displayInfo(){
    // automatically returns empty string if no value, null if field does not exist

    let gameName1 = localStorage.getItem("gameName_1");
    let gameDate1 = localStorage.getItem("gameDate_1");
    let playerNum1 = localStorage.getItem("playerNum_1");
    let pairNum1 = localStorage.getItem("pairNum_1");
    let roundNum1 = localStorage.getItem("roundNum_1");

    let blackTotal = localStorage.getItem("blackTotal");
    let blackAverage = localStorage.getItem("blackAverage");
    let redTotal = localStorage.getItem("redTotal");
    let redAverage = localStorage.getItem("redAverage");

    let highestName = localStorage.getItem("highestName");
    let highestScore = localStorage.getItem("highestScore");
    let lowestName = localStorage.getItem("lowestName");
    let lowestScore = localStorage.getItem("lowestScore");

    let highestBlackName = localStorage.getItem("highestBlackName");
    let highestBlackScore = localStorage.getItem("highestBlackScore");
    let lowestBlackName = localStorage.getItem("lowestBlackName");
    let lowestBlackScore = localStorage.getItem("lowestBlackScore");
    let highestRedName = localStorage.getItem("highestRedName");
    let highestRedScore = localStorage.getItem("highestRedScore");
    let lowestRedName = localStorage.getItem("lowestRedName");
    let lowestRedScore = localStorage.getItem("lowestRedScore");

    document.getElementById('studentHeader').innerHTML = "Game (" + gameName1 + ")";

    document.getElementById('gameName_1').innerHTML = gameName1;
    document.getElementById('gameDate_1').innerHTML = gameDate1;
    document.getElementById('playerNum_1').innerHTML = playerNum1;
    document.getElementById('pairNum_1').innerHTML = pairNum1;
    document.getElementById('roundNum_1').innerHTML = roundNum1;

    document.getElementById('blackTotal').innerHTML = blackTotal;
    document.getElementById('blackAverage').innerHTML = blackAverage;
    document.getElementById('redTotal').innerHTML = redTotal;
    document.getElementById('redAverage').innerHTML = redAverage;

    document.getElementById('highestName').innerHTML = highestName;
    document.getElementById('highestScore').innerHTML = highestScore;
    document.getElementById('lowestName').innerHTML = lowestName;
    document.getElementById('lowestScore').innerHTML = lowestScore;

    document.getElementById('highestBlackName').innerHTML = highestBlackName;
    document.getElementById('highestBlackScore').innerHTML = highestBlackScore;
    document.getElementById('lowestBlackName').innerHTML = lowestBlackName;
    document.getElementById('lowestBlackScore').innerHTML = lowestBlackScore;
    document.getElementById('highestRedName').innerHTML = highestRedName;
    document.getElementById('highestRedScore').innerHTML = highestRedScore;
    document.getElementById('lowestRedName').innerHTML = lowestRedName;
    document.getElementById('lowestRedScore').innerHTML = lowestRedScore;
}