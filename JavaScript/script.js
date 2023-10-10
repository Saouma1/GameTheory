// if we need to delay anything by a number of milliseconds
// wait(1000) delays for 1 second
var wait = (ms) => {
    const start = Date.now();
    let now = start;
    while (now - start < ms) {
      now = Date.now();
    }
}

let usernameValue = "";
let passwordValue = ""; // very secure, don't worry about it

function submitForm(){ // this DOES trigger
    if(document.getElementById("username") != null){
        usernameValue = document.getElementById('username').value;
        sessionStorage.setItem("username", usernameValue); // AND THIS IS HOW WE SAVE IT within a session
            // localStorage saves between sessions
    }
    else{
        usernameValue = "submitForm() NULL";
    }
    if(document.getElementById("password") != null){
        passwordValue = document.getElementById('password').value;
        sessionStorage.setItem("password", passwordValue);
    }
    else{
        passwordValue = "submitForm() NULL";
    }
}

function logInfo(){
    // automatically returns empty string if no value
    console.log(sessionStorage.getItem("username")); // and this is how we retrieve it
    console.log(sessionStorage.getItem("password"));
}

if(document.getElementById("courseLogo")){
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
    cssCrossLeft.style.left = (cll + (borderRadius/4)) + "px";
    cssCrossLeft.style.top = (clt + (borderRadius/4)) + "px";
    cssCrossRight.style.width = (clw * 1.375) + "px";
    cssCrossRight.style.left = (cll + (borderRadius/4)) + "px";
    cssCrossRight.style.top = (clt + (borderRadius/4)) + "px";

    courseCrossLeft.style.width = (clw2 * 1.375) + "px";
    courseCrossLeft.style.left = (cll2 + (borderRadius/4)) + "px";
    courseCrossLeft.style.top = (clt2 + (borderRadius/4)) + "px";
    courseCrossRight.style.width = (clw2 * 1.375) + "px";
    courseCrossRight.style.left = (cll2 + (borderRadius/4)) + "px";
    courseCrossRight.style.top = (clt2 + (borderRadius/4)) + "px";
}