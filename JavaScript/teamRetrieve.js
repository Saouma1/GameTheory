function displayInfoRedBlack(){
    // find out what the contents of each card cell are so that we can color the background appropriately
    let cardElements = document.querySelectorAll(".card");
    for (let i in cardElements){
        if(cardElements[i].innerHTML == "R"){
            document.getElementById(cardElements[i].id).style.backgroundColor = "pink";
        }
        if(cardElements[i].innerHTML == "B"){
            document.getElementById(cardElements[i].id).style.backgroundColor = "gray";
        }
    }

    // find date, player names in url
    let urlFull = window.location.href; // full url
    // isolate end of url, which contains fname and lname info
    let dateIndex = urlFull.search("d=");
    let gameDate = urlFull.slice(dateIndex + 2);
    let dateEndIndex = gameDate.search("&");
    gameDate = gameDate.substring(0, dateEndIndex);
    let p1Index = urlFull.search("p1="); // index of how much to cut off from front
    let pNames = urlFull.slice(p1Index + 3); // slice out fname variable, adding length of "fname="
    let p1EndIndex = pNames.search("&"); // index of when lname variable starts
    let p1Name = pNames.substring(0, p1EndIndex); // extract first name
    let p2Index = pNames.search("p2="); // search for beginning of fname
    let p2Name = pNames.slice(p2Index + 3); // slice everything before last name
    //p1Name = p1Name.charAt(0).toUpperCase() + p1Name.substr(1).toLowerCase();
    document.getElementById('playerOneName').innerHTML = p1Name;
    document.getElementById('headerPlayerOne').innerHTML = p1Name;
    document.getElementById('tablePlayerOneCard').innerHTML = p1Name;
    document.getElementById('tablePlayerOnePoints').innerHTML = p1Name;
    document.getElementById('playerTwoName').innerHTML = p2Name;
    document.getElementById('headerPlayerTwo').innerHTML = p2Name;
    document.getElementById('tablePlayerTwoCard').innerHTML = p2Name;
    document.getElementById('tablePlayerTwoPoints').innerHTML = p2Name;
    document.getElementById('gameDate').innerHTML = gameDate;
}
function displayInfoWheatSteel(){
    // find date, player names in url
    let urlFull = window.location.href; // full url
    // isolate end of url, which contains date and team info
    let dateIndex = urlFull.search("date=");
    let gameDate = urlFull.slice(dateIndex + 5);
    let dateEndIndex = gameDate.search("&");
    gameDate = gameDate.substring(0, dateEndIndex);
    document.getElementById('gameDate').innerHTML = gameDate;

    // grab team name from url
    let teamIndex = (urlFull.search("t=") + 2);
    let teamEndIndex = (urlFull.length);
    let gameTeam = urlFull.substring(teamIndex, teamEndIndex);
    gameTeam = decodeURI(gameTeam); // turn %20 back into a space
    //console.log(gameTeam);
    document.getElementById('teamName').innerHTML = gameTeam;

    let roundOneWheat = localStorage.getItem("roundOneWheat");
    let roundTwoWheat = localStorage.getItem("roundTwoWheat");
    let roundThreeWheat = localStorage.getItem("roundThreeWheat");
    let roundFourWheat = localStorage.getItem("roundFourWheat");
    let roundFiveWheat = localStorage.getItem("roundFiveWheat");

    document.getElementById('roundOneWheat').innerHTML = roundOneWheat;
    document.getElementById('roundTwoWheat').innerHTML = roundTwoWheat;
    document.getElementById('roundThreeWheat').innerHTML = roundThreeWheat;
    document.getElementById('roundFourWheat').innerHTML = roundFourWheat;
    document.getElementById('roundFiveWheat').innerHTML = roundFiveWheat;

    let roundOneSteel = localStorage.getItem("roundOneSteel");
    let roundTwoSteel = localStorage.getItem("roundTwoSteel");
    let roundThreeSteel = localStorage.getItem("roundThreeSteel");
    let roundFourSteel = localStorage.getItem("roundFourSteel");
    let roundFiveSteel = localStorage.getItem("roundFiveSteel");

    document.getElementById('roundOneSteel').innerHTML = roundOneSteel;
    document.getElementById('roundTwoSteel').innerHTML = roundTwoSteel;
    document.getElementById('roundThreeSteel').innerHTML = roundThreeSteel;
    document.getElementById('roundFourSteel').innerHTML = roundFourSteel;
    document.getElementById('roundFiveSteel').innerHTML = roundFiveSteel;

    let roundOneWheatTrade = localStorage.getItem("roundOneWheatTrade");
    let roundTwoWheatTrade = localStorage.getItem("roundTwoWheatTrade");
    let roundThreeWheatTrade = localStorage.getItem("roundThreeWheatTrade");
    let roundFourWheatTrade = localStorage.getItem("roundFourWheatTrade");
    let roundFiveWheatTrade = localStorage.getItem("roundFiveWheatTrade");

    document.getElementById('roundOneWheatTrade').innerHTML = roundOneWheatTrade;
    document.getElementById('roundTwoWheatTrade').innerHTML = roundTwoWheatTrade;
    document.getElementById('roundThreeWheatTrade').innerHTML = roundThreeWheatTrade;
    document.getElementById('roundFourWheatTrade').innerHTML = roundFourWheatTrade;
    document.getElementById('roundFiveWheatTrade').innerHTML = roundFiveWheatTrade;

    let roundOneSteelTrade = localStorage.getItem("roundOneSteelTrade");
    let roundTwoSteelTrade = localStorage.getItem("roundTwoSteelTrade");
    let roundThreeSteelTrade = localStorage.getItem("roundThreeSteelTrade");
    let roundFourSteelTrade = localStorage.getItem("roundFourSteelTrade");
    let roundFiveSteelTrade = localStorage.getItem("roundFiveSteelTrade");

    document.getElementById('roundOneSteelTrade').innerHTML = roundOneSteelTrade;
    document.getElementById('roundOneSteelTrade').innerHTML = roundOneSteelTrade;
    document.getElementById('roundOneSteelTrade').innerHTML = roundOneSteelTrade;
    document.getElementById('roundOneSteelTrade').innerHTML = roundOneSteelTrade;
    document.getElementById('roundOneSteelTrade').innerHTML = roundOneSteelTrade;

    let roundOneWheatConsume = localStorage.getItem("roundOneWheatConsume");
    let roundTwoWheatConsume = localStorage.getItem("roundTwoWheatConsume");
    let roundThreeWheatConsume = localStorage.getItem("roundThreeWheatConsume");
    let roundFourWheatConsume = localStorage.getItem("roundFourWheatConsume");
    let roundFiveWheatConsume = localStorage.getItem("roundFiveWheatConsume");

    document.getElementById('roundOneWheatConsume').innerHTML = roundOneWheatConsume;
    document.getElementById('roundTwoWheatConsume').innerHTML = roundTwoWheatConsume;
    document.getElementById('roundThreeWheatConsume').innerHTML = roundThreeWheatConsume;
    document.getElementById('roundFourWheatConsume').innerHTML = roundFourWheatConsume;
    document.getElementById('roundFiveWheatConsume').innerHTML = roundFiveWheatConsume;

    let roundOneSteelConsume = localStorage.getItem("roundOneSteelConsume");
    let roundTwoSteelConsume = localStorage.getItem("roundTwoSteelConsume");
    let roundThreeSteelConsume = localStorage.getItem("roundThreeSteelConsume");
    let roundFourSteelConsume = localStorage.getItem("roundFourSteelConsume");
    let roundFiveSteelConsume = localStorage.getItem("roundFiveSteelConsume");

    document.getElementById('roundOneSteelConsume').innerHTML = roundOneSteelConsume;
    document.getElementById('roundTwoSteelConsume').innerHTML = roundTwoSteelConsume;
    document.getElementById('roundThreeSteelConsume').innerHTML = roundThreeSteelConsume;
    document.getElementById('roundFourSteelConsume').innerHTML = roundFourSteelConsume;
    document.getElementById('roundFiveSteelConsume').innerHTML = roundFiveSteelConsume;
}