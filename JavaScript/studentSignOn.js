function displayInfoInformation(){
    let gameName1 = localStorage.getItem("firstNameDisplay");
    let gameDate1 = localStorage.getItem("lastNameDisplay");
    let playerNum1 = localStorage.getItem("emailDisplay");

    if(gameName1) document.getElementById('firstNameDisplay').innerHTML = gameName1;
    if(gameDate1) document.getElementById('lastNameDisplay').innerHTML = gameDate1;
    if(playerNum1) document.getElementById('emailDisplay').innerHTML = playerNum1;
}
