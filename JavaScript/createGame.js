// create test data

// https://www.section.io/engineering-education/how-to-use-localstorage-with-javascript/
    // look into storing objects in localStorage to better transition to DB

// red-black student view
localStorage.setItem("redBlackNum", 3);  // TODO: add record count instead of static number
localStorage.setItem("redBlackHigh", 900); // TODO: add max instead of static number
localStorage.setItem("redBlackLow", 400); // TODO: add min instead of static number
localStorage.setItem("redBlackGPA", 633.33); // TODO: add calculation instead of static number
// wheat-steel student view
localStorage.setItem("wheatSteelNum", 2); // TODO: add record count instead of static number
localStorage.setItem("wheatHigh", 100); // TODO: add max instead of static number
localStorage.setItem("wheatGoalNum", 1 + "/" + 2 + " (" + 50 + "%)"); // TODO: add calculation instead of static numbers
localStorage.setItem("steelHigh", 750); // TODO: add max instead of static number
localStorage.setItem("steelGoalNum", 2 + "/" + 2 + " (" + 100 + "%)"); // TODO: add calculation instead of static numbers

/* red-black game view */
// game info
localStorage.setItem("gameName_1", "Red Card Black Card");
localStorage.setItem("gameDate_1", "10/13/23");
localStorage.setItem("playerNum_1", 24); 
localStorage.setItem("pairNum_1", 12);
localStorage.setItem("roundNum_1", 8);
// game stats 
localStorage.setItem("blackTotal", 88); 
localStorage.setItem("blackAverage", 11);
localStorage.setItem("redTotal", 104); 
localStorage.setItem("redAverage", 13);
// player stats (general)
localStorage.setItem("highestName", "Jack");
localStorage.setItem("highestScore", 1400);
localStorage.setItem("lowestName", "Sam");
localStorage.setItem("lowestScore", 200);
// player stats (specific)
localStorage.setItem("highestBlackName", "Sam");
localStorage.setItem("highestBlackScore", 7);
localStorage.setItem("lowestBlackName", "Jack");
localStorage.setItem("lowestBlackScore", 1);
localStorage.setItem("highestRedName", "Jack");
localStorage.setItem("highestRedScore", 7);
localStorage.setItem("lowestRedName", "Sam");
localStorage.setItem("lowestRedScore", 1);

/* wheat-steel game view */
// game info
localStorage.setItem("gameName_2", "Wheat & Steel");
localStorage.setItem("gameDate_2", "10/16/23");
localStorage.setItem("playerNum_2", 24); 
localStorage.setItem("teamsFour", 6);
localStorage.setItem("periodNum", 5);
// game results
localStorage.setItem("bothGoals", ["The Dealers", "Jad"]); 
localStorage.setItem("oneGoal", ["Economics", "The Traders", "Steal", "Wheat"]); 
localStorage.setItem("noGoals", ["Micro", "Wall Street"]); 
// wheat stats
localStorage.setItem("wheatProduceTotal", 1849);
localStorage.setItem("wheatProduceAverage", 369.8);
localStorage.setItem("wheatConsumeTotal", 1849);
localStorage.setItem("wheatConsumeAverage", 369.8);
localStorage.setItem("wheatTradeTotal", 613);
localStorage.setItem("wheatTradeAverage", 122.6);
// steel stats
localStorage.setItem("steelProduceTotal", 1849);
localStorage.setItem("steelProduceAverage", 369.8);
localStorage.setItem("steelConsumeTotal", 1849);
localStorage.setItem("steelConsumeAverage", 369.8);
localStorage.setItem("steelTradeTotal", 613);
localStorage.setItem("steelTradeAverage", 122.6);

// game info
localStorage.setItem("roundOneWheat", 0);
localStorage.setItem("roundTwoWheat", 0);
localStorage.setItem("roundThreeWheat", 0);
localStorage.setItem("roundFourWheat", 0);
localStorage.setItem("roundFiveWheat", 0);

localStorage.setItem("roundOneSteel", 150);
localStorage.setItem("roundTwoSteel", 150);
localStorage.setItem("roundThreeSteel", 150);
localStorage.setItem("roundFourSteel", 150);
localStorage.setItem("roundFiveSteel", 150);

localStorage.setItem("roundOneWheatTrade", "-");
localStorage.setItem("roundTwoWheatTrade", 0);
localStorage.setItem("roundThreeWheatTrade", 0);
localStorage.setItem("roundFourWheatTrade", "+100");
localStorage.setItem("roundFiveWheatTrade", "+100");

localStorage.setItem("roundOneSteelTrade", "-");
localStorage.setItem("roundTwoSteelTrade", 0);
localStorage.setItem("roundThreeSteelTrade", 0);
localStorage.setItem("roundFourSteelTrade", "-150");
localStorage.setItem("roundFiveSteelTrade", "-150");

localStorage.setItem("roundOneWheatConsume", 0);
localStorage.setItem("roundTwoWheatConsume", 0);
localStorage.setItem("roundThreeWheatConsume", 0);
localStorage.setItem("roundFourWheatConsume", 100);
localStorage.setItem("roundFiveWheatConsume", 100);

localStorage.setItem("roundOneSteelConsume", 150);
localStorage.setItem("roundTwoSteelConsume", 150);
localStorage.setItem("roundThreeSteelConsume", 150);
localStorage.setItem("roundFourSteelConsume", 0);
localStorage.setItem("roundFiveSteelConsume", 0);