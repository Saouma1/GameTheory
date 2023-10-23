function displayInfo(){
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

    // find player names in url
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