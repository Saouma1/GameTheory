// find max ID in DB

function displayInfo(){
    // automatically returns empty string if no value, null if field does not exist
        // let firstName = localStorage.getItem("firstName"); // retrieves it from the current session
        // let lastName = localStorage.getItem("lastName");
    // use first and last name pulled from url to identify record in storage
    let email = localStorage.getItem("email");
    
    let redBlackNum = localStorage.getItem("redBlackNum");
    let redBlackHigh = localStorage.getItem("redBlackHigh");
    let redBlackLow = localStorage.getItem("redBlackLow");
    let redBlackGPA = localStorage.getItem("redBlackGPA");

    let wheatSteelNum = localStorage.getItem("wheatSteelNum");
    let wheatHigh = localStorage.getItem("wheatHigh");
    let wheatGoalNum = localStorage.getItem("wheatGoalNum");
    let steelHigh = localStorage.getItem("steelHigh");
    let steelGoalNum = localStorage.getItem("steelGoalNum");

    let urlFull = window.location.href; // full url
    // isolate end of url, which contains fname and lname info
    let urlFnameIndex = urlFull.search("fname="); // index of how much to cut off from front
    let urlNames = urlFull.slice(urlFnameIndex + 6); // slice out fname variable, adding length of "fname="
    let urlFNameEndIndex = urlNames.search("&"); // index of when lname variable starts
    let urlFName = urlNames.substring(0, urlFNameEndIndex); // extract first name
    let urlLNameIndex = urlNames.search("="); // search for beginning of fname
    let urlLName = urlNames.slice(urlLNameIndex + 1); // slice everything before last name

    // display information from url
    document.getElementById('studentHeader').innerHTML = "Student (" + urlFName + " " + urlLName + ")";
    document.getElementById('firstNameDisplay').innerHTML = urlFName;
    document.getElementById('lastNameDisplay').innerHTML = urlLName;

    // now display information from localStorage using url info as IDs
    document.getElementById('emailDisplay').innerHTML = email;

    document.getElementById('redBlackNum').innerHTML = redBlackNum;
    document.getElementById('redBlackHigh').innerHTML = redBlackHigh;
    document.getElementById('redBlackLow').innerHTML = redBlackLow;
    document.getElementById('redBlackGPA').innerHTML = redBlackGPA;

    document.getElementById('wheatSteelNum').innerHTML = wheatSteelNum;
    document.getElementById('wheatHigh').innerHTML = wheatHigh;
    document.getElementById('wheatGoalNum').innerHTML = wheatGoalNum;
    document.getElementById('steelHigh').innerHTML = steelHigh;
    document.getElementById('steelGoalNum').innerHTML = steelGoalNum;
    
}