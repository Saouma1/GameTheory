let firstNameValue = "";
let lastNameValue = "";
let emailValue = "";
let numRecords = (localStorage.length / 3); // three fields per record

// is there a better spot than sessionStorage to put this information?
    // once we get a DB, insert it there
// should there be a unique ID in the session storage as well?
    // could use numRecords

function submitForm(){ // rewrites any values previously stored
    // make sure at least one is not null
    if (document.getElementById("firstName") != null || document.getElementById("lastName") != null || document.getElementById("email") != null){
        if(document.getElementById("firstName") != null){
            firstNameValue = document.getElementById('firstName').value;
        }
        else{
            firstNameValue = "n/a";
        }
        if(document.getElementById("lastName") != null){
            lastNameValue = document.getElementById('lastName').value;
        }
        else{
            lastNameValue = "n/a";
        }
        if(document.getElementById("email") != null){
            emailValue = document.getElementById('email').value;
        }
        else{
            emailValue = "n/a";
        }
        localStorage.setItem("firstName", firstNameValue); 
        localStorage.setItem("lastName", lastNameValue);
        localStorage.setItem("email", emailValue);
    }
    // sessionStorage saves in the current session
        // localStorage saves between sessions
}

function displayInfo(){
    // automatically returns empty string if no value, null if field does not exist
    let firstName = localStorage.getItem("firstName"); // retrieves it from the current session
    let lastName = localStorage.getItem("lastName");
    let email = localStorage.getItem("email");

    document.getElementById('firstNameDisplay').innerHTML = "First Name: " + firstName;
    document.getElementById('lastNameDisplay').innerHTML = "Last Name: " + lastName;
    document.getElementById('emailDisplay').innerHTML = "CSS Email: " + email;
}