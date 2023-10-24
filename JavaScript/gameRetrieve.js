// use ID in DB

function displayInfoRedBlack(){
    //let gameName1 = localStorage.getItem("gameName_1");
    //let gameDate1 = localStorage.getItem("gameDate_1");
        // use URL to grab date + use it to search storage
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

    let urlFull = window.location.href; // full url
    // isolate end of url, which contains date info
    let dateIndex = urlFull.search("date="); // index of how much to cut off from front
    let urlDate = urlFull.slice(dateIndex + 5); // slice out dateIndex variable, adding length of "date="

   // document.getElementById('studentHeader_1').innerHTML = "Game (" + gameName1 + ")";
    //document.getElementById('gameName_1').innerHTML = gameName1;
    document.getElementById('gameDate_1').innerHTML = urlDate;
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

    // use date grabbed from url and add it to pair links
    let allLinks = document.getElementsByTagName("a");
    for (link in allLinks){
        let targetLink = allLinks[link].href
        // check if link leads to redBlackPairView and only edit it if true
        if (typeof targetLink !== 'undefined' && targetLink.search("redBlackPairView.html") > 0){
            let insertIndex = targetLink.search("d="); // index of how much to cut off from front
            let insertString = targetLink.slice(insertIndex + 2); // slice out dateIndex variable, adding length of "d="
            let preData = targetLink.substring(0, insertIndex); // with date
            let insertIndexEnd = insertString.search("&");
            insertString = insertString.slice(insertIndexEnd + 1); // without date
            // insert new date where old date was
            insertString = urlDate + "&" + insertString;
            let linkNew = preData + "d=" + insertString;
            // now replace link
            allLinks[link].href = linkNew;
        }
    }
}
function displayInfoWheatSteel(){
    let gameName2 = localStorage.getItem("gameName_2");
    //let gameDate2 = localStorage.getItem("gameDate_2");
        // use URL to grab date + use it to search storage
    let playerNum2 = localStorage.getItem("playerNum_2");
    let teamsFour = localStorage.getItem("teamsFour");
    let periodNum = localStorage.getItem("periodNum");

    // set in local storage as one string
        // need to loop through the array to display a space after each name
    let bothGoals = localStorage.getItem("bothGoals");
    for (let i = 0; i < bothGoals.length; i++) {
        if (bothGoals[i] == ","){
            bothGoals = bothGoals.slice(0, (i + 1)) + " " + bothGoals.slice((i + 1));
        }
    }
    let oneGoal = localStorage.getItem("oneGoal");
    for (let i = 0; i < oneGoal.length; i++) {
        if (oneGoal[i] == ","){
            oneGoal = oneGoal.slice(0, (i + 1)) + " " + oneGoal.slice((i + 1));
        }
    }
    let noGoals = localStorage.getItem("noGoals");
    for (let i = 0; i < noGoals.length; i++) {
        if (noGoals[i] == ","){
            noGoals = noGoals.slice(0, (i + 1)) + " " + noGoals.slice((i + 1));
        }
    }

    let wheatProduceTotal = localStorage.getItem("wheatProduceTotal");
    let wheatProduceAverage = localStorage.getItem("wheatProduceAverage");
    let wheatConsumeTotal = localStorage.getItem("wheatConsumeTotal");
    let wheatConsumeAverage = localStorage.getItem("wheatConsumeAverage");
    let wheatTradeTotal = localStorage.getItem("wheatTradeTotal");
    let wheatTradeAverage = localStorage.getItem("wheatTradeAverage");

    let steelProduceTotal = localStorage.getItem("steelProduceTotal");
    let steelProduceAverage = localStorage.getItem("steelProduceAverage");
    let steelConsumeTotal = localStorage.getItem("steelConsumeTotal");
    let steelConsumeAverage = localStorage.getItem("steelConsumeAverage");
    let steelTradeTotal = localStorage.getItem("steelTradeTotal");
    let steelTradeAverage = localStorage.getItem("steelTradeAverage");

    let urlFull = window.location.href; // full url
    // isolate end of url, which contains date info
    let dateIndex = urlFull.search("date="); // index of how much to cut off from front
    let urlDate = urlFull.slice(dateIndex + 5); // slice out dateIndex variable, adding length of "date="

    document.getElementById('studentHeader_2').innerHTML = "Game (" + gameName2 + ")";
    document.getElementById('gameName_2').innerHTML = gameName2;
    document.getElementById('gameDate_2').innerHTML = urlDate;
    document.getElementById('playerNum_2').innerHTML = playerNum2;
    document.getElementById('teamsFour').innerHTML = teamsFour;
    document.getElementById('periodNum').innerHTML = periodNum;

    document.getElementById('bothGoalNames').innerHTML = bothGoals;
    document.getElementById('oneGoalNames').innerHTML = oneGoal;
    document.getElementById('noGoalNames').innerHTML = noGoals;

    document.getElementById('wheatProduceTotal').innerHTML = wheatProduceTotal;
    document.getElementById('wheatProduceAverage').innerHTML = wheatProduceAverage;
    document.getElementById('wheatConsumeTotal').innerHTML = wheatConsumeTotal;
    document.getElementById('wheatConsumeAverage').innerHTML = wheatConsumeAverage;
    document.getElementById('wheatTradeTotal').innerHTML = wheatTradeTotal;
    document.getElementById('wheatTradeAverage').innerHTML = wheatTradeAverage;

    document.getElementById('steelProduceTotal').innerHTML = steelProduceTotal;
    document.getElementById('steelProduceAverage').innerHTML = steelProduceAverage;
    document.getElementById('steelConsumeTotal').innerHTML = steelConsumeTotal;
    document.getElementById('steelConsumeAverage').innerHTML = steelConsumeAverage;
    document.getElementById('steelTradeTotal').innerHTML = steelTradeTotal;
    document.getElementById('steelTradeAverage').innerHTML = steelTradeAverage;

    // now make team links dynamic
    // adding ending character to each list of names
        // make sure that team names do not have special characters
    let bothSpan = document.getElementById("bothGoalNames").innerHTML + ".";
    let oneSpan = document.getElementById("oneGoalNames").innerHTML + ".";
    let noSpan = document.getElementById("noGoalNames").innerHTML + ".";

    let teamsBoth = [];
    let teamsOne = [];
    let teamsNone = [];

    // create array of teams for each of the three category
    let teamNameFull = "";
    for (teamName in bothSpan){
        // have to split looking for ", " into two tied expressions
        if (bothSpan[teamName] != ","  && bothSpan[teamName] != "."){
            teamNameFull = teamNameFull + bothSpan[teamName];
        }
        else{
            teamsBoth.push(teamNameFull);
            teamNameFull = ""; // reset string
        } 
    }
    // grab date, team name from URL using urlFull strat
    for (team in teamsBoth){
        if (team < 1){
            teamsBoth[team] = "<a href='wheatSteelTeamView.html?date=" + urlDate + "&t="+teamsBoth[team].toString()+"'>" + teamsBoth[team] + "</a>";
            // see if toString is necessary
        }
        else{
            // remove space from team name for processing
            teamsBoth[team] = teamsBoth[team].substring(1);
            // add space before link for display
            teamsBoth[team] = " <a href='wheatSteelTeamView.html?date=" + urlDate + "&t="+teamsBoth[team].toString() + "'>" + teamsBoth[team] + "</a>";
        }
    }
    // set the span.innerHTML to array
    document.getElementById("bothGoalNames").innerHTML = teamsBoth;

    for (teamName in oneSpan){
        if (oneSpan[teamName] != ","  && oneSpan[teamName] != "."){
            teamNameFull = teamNameFull + oneSpan[teamName];
        }
        else{
            teamsOne.push(teamNameFull);
            teamNameFull = ""; // reset string
        } 
    }
    for (team in teamsOne){
        if (team < 1){
            teamsOne[team] = "<a href='wheatSteelTeamView.html?date=" + urlDate + "&t="+teamsOne[team].toString()+"'>" + teamsOne[team] + "</a>";
        }
        else{
            // remove space from team name for processing
            teamsOne[team] = teamsOne[team].substring(1);
            // add space before link for display
            teamsOne[team] = " <a href='wheatSteelTeamView.html?date=" + urlDate + "&t="+teamsOne[team].toString() + "'>" + teamsOne[team] + "</a>";
        }
    }
    document.getElementById("oneGoalNames").innerHTML = teamsOne;

    for (teamName in noSpan){
        if (noSpan[teamName] != ","  && noSpan[teamName] != "."){
            teamNameFull = teamNameFull + noSpan[teamName];
        }
        else{
            teamsNone.push(teamNameFull);
            teamNameFull = ""; // reset string
        } 
    }
    for (team in teamsNone){
        if (team < 1){
            teamsNone[team] = "<a href='wheatSteelTeamView.html?date=" + urlDate + "&t="+teamsNone[team].toString()+"'>" + teamsNone[team] + "</a>";
        }
        else{
            // remove space from team name for processing
            teamsNone[team] = teamsNone[team].substring(1);
            // add space before link for display
            teamsNone[team] = " <a href='wheatSteelTeamView.html?date=" + urlDate + "&t=" + teamsNone[team].toString() + "'>" + teamsNone[team] + "</a>";
        }
    }
    document.getElementById("noGoalNames").innerHTML = teamsNone;
}