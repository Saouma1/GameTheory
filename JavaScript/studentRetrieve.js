// find max ID in DB

function displayInfo(){
    // automatically returns empty string if no value, null if field does not exist
    let firstName = localStorage.getItem("firstName"); // retrieves it from the current session
    let lastName = localStorage.getItem("lastName");
    let email = localStorage.getItem("email");

    document.getElementById('studentHeader').innerHTML = "Student (" + firstName + " " + lastName + ")";
    document.getElementById('firstNameDisplay').innerHTML = "First Name: " + firstName;
    document.getElementById('lastNameDisplay').innerHTML = "Last Name: " + lastName;
    document.getElementById('emailDisplay').innerHTML = "CSS Email: " + email;
}