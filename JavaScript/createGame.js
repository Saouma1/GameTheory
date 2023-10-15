// create test data

// red-black
localStorage.setItem("redBlackNum", 3);  // TODO: add record count instead of static number
localStorage.setItem("redBlackHigh", 900); // TODO: add max instead of static number
localStorage.setItem("redBlackLow", 400); // TODO: add min instead of static number
localStorage.setItem("redBlackGPA", 633.33); // TODO: add calculation instead of static number
// wheat-steel
localStorage.setItem("wheatSteelNum", 2); // TODO: add record count instead of static number
localStorage.setItem("wheatHigh", 100); // TODO: add max instead of static number
localStorage.setItem("wheatGoalNum", 1 + "/" + 2 + " (" + 50 + "%)"); // TODO: add calculation instead of static numbers
localStorage.setItem("steelHigh", 750); // TODO: add max instead of static number
localStorage.setItem("steelGoalNum", 2 + "/" + 2 + " (" + 100 + "%)"); // TODO: add calculation instead of static numbers

localStorage.removeItem("firstName_2");
localStorage.removeItem("lastName_2");
localStorage.removeItem("email_2");

localStorage.removeItem("firstName_3");
localStorage.removeItem("lastName_3");
localStorage.removeItem("email_3");

/*
Number of Red/Black Games Played: <span id="redBlackNum">0</span> <br> <!---3--->
Highest Final Score: <span id="redBlackHigh">0</span> <br><!---900--->
Lowest Final Score: <span id="redBlackLow">0</span> <br><!---400--->
Game Point Average: <span id="redBlackGPA">0</span> <br> <!--633.33--->

Number of Wheat & Steel Games Played: <span id="wheatSteelNum">0</span> <br> <!---2--->
Highest Total Wheat Production: <span id="wheatHigh">0</span> <br> <!---100--->
Wheat Consumption Goals Met: <span id="wheatGoalNum">0</span> <br> <!--- 1 / 2 (50%) --->
Highest Total Steel Production: <span id="steelHigh">0</span> <br> <!---750--->
Steel Consumption Goals Met: <span id="steelGoalNum">0</span> <br> <!--- 2 / 2 (100%) --->
*/