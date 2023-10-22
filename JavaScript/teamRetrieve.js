function displayInfo(){
    // find out what the contents of each card cell are so that we can color the background appropriately
    let cardElements = document.querySelectorAll(".card");
    for (let i in cardElements){
        console.log(cardElements[i].innerHTML);
        document.getElementById(cardElements[i].id).style.color = "black";
        if(cardElements[i].innerHTML == "R"){
            document.getElementById(cardElements[i].id).style.backgroundColor = "pink";
        }
        if(cardElements[i].innerHTML == "B"){
            document.getElementById(cardElements[i].id).style.backgroundColor = "gray";
        }
    }
}