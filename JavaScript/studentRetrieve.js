// find max ID in DB

function displayInfo(){
    // automatically returns empty string if no value, null if field does not exist
    let firstName = localStorage.getItem("firstName"); // retrieves it from the current session
    let lastName = localStorage.getItem("lastName");
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

    document.getElementById('studentHeader').innerHTML = "Student (" + firstName + " " + lastName + ")";
    document.getElementById('firstNameDisplay').innerHTML = firstName;
    document.getElementById('lastNameDisplay').innerHTML = lastName;
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